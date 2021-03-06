<?php

namespace Drupal\commerce_order\Form;

use Drupal\commerce_order\Entity\OrderType;
use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides an order type form.
 */
class OrderTypeForm extends BundleEntityFormBase {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\commerce_order\Entity\OrderTypeInterface $order_type */
    $order_type = $this->entity;
    $workflow_manager = \Drupal::service('plugin.manager.workflow');
    $workflows = $workflow_manager->getGroupedLabels('commerce_order');

    $form['#tree'] = TRUE;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $order_type->label(),
      '#description' => $this->t('Label for the order type.'),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $order_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_order\Entity\OrderType::load',
        'source' => ['label'],
      ],
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
    ];
    $form['workflow'] = [
      '#type' => 'select',
      '#title' => t('Workflow'),
      '#options' => $workflows,
      '#default_value' => $order_type->getWorkflowId(),
      '#description' => $this->t('Used by all orders of this type.'),
    ];

    $form['refresh'] = [
      '#type' => 'details',
      '#title' => t('Order refresh'),
      '#weight' => 5,
      '#open' => TRUE,
      '#collapsible' => TRUE,
      '#tree' => FALSE,
    ];

    $form['refresh']['refresh_intro'] = [
      '#markup' => '<p>' . t('These settings let you control how draft orders are refreshed, the process during which order item prices are recalculated.') . '</p>',
    ];
    $form['refresh']['refresh_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Order refresh mode'),
      '#options' => [
        OrderType::REFRESH_ALWAYS => t('Refresh a draft order when it is loaded regardless of who it belongs to.'),
        OrderType::REFRESH_OWNER => t('Only refresh a draft order when it is loaded if it belongs to the current user.'),
      ],
      '#default_value' => ($order_type->isNew()) ? OrderType::REFRESH_ALWAYS : $order_type->getRefreshMode(),
    ];
    $form['refresh']['refresh_frequency'] = [
      '#type' => 'textfield',
      '#title' => t('Order refresh frequency'),
      '#description' => t('Draft orders will only be refreshed if more than the specified number of seconds have passed since they were last refreshed.'),
      '#default_value' => ($order_type->isNew()) ? 30 : $order_type->getRefreshFrequency(),
      '#required' => TRUE,
      '#size' => 10,
      '#field_suffix' => t('seconds'),
    ];

    return $this->protectBundleIdElement($form);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\state_machine\WorkflowManager $workflow_manager */
    $workflow_manager = \Drupal::service('plugin.manager.workflow');
    /** @var \Drupal\state_machine\Plugin\Workflow\WorkflowInterface $workflow */
    $workflow = $workflow_manager->createInstance($form_state->getValue('workflow'));
    if (!$workflow->getTransition('place')) {
      $form_state->setError($form['workflow'], $this->t('The @workflow workflow does not have a "Place" transition.', [
        '@workflow' => $workflow->getLabel(),
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = $this->entity->save();
    drupal_set_message($this->t('Saved the %label order type.', ['%label' => $this->entity->label()]));
    $form_state->setRedirect('entity.commerce_order_type.collection');

    if ($status == SAVED_NEW) {
      commerce_order_add_order_items_field($this->entity);
    }
  }

}
