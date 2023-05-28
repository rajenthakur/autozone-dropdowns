<?php

namespace Drupal\autozone\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Http\Client\Exception;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface; 
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;

class AutozoneForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dependent_dropdown_Form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    
    $opt = static::foodCategory();
    
    if (empty($form_state->getValue('category'))) {
        $cat = "none";
    }
    else {
        $cat = $form_state->getValue('category');
    }
    if (empty($form_state->getValue('availableitems'))) {
        $cat2 = "none";
    }
    else {
        $cat2 = $form_state->getValue('availableitems');
    }
    if (empty($form_state->getValue('secondavailableitems'))) {
        $cat3 = "none";
    }
    else {
        $cat3 = $form_state->getValue('secondavailableitems');
    }

    $form['category'] = [
        '#type' => 'select',
        '#title' => 'Years',
        '#options' => $opt,
        'default_value' => $cat,
        '#ajax' => [
            'callback' => '::DropdownCallback',
            'wrapper' => 'field-container',
            'event' => 'change'
        ]
    ];
    $form['availableitems'] = [
        '#type' => 'select',
        '#title' => 'Makes',
        '#options' =>static::availableItems($cat),
        '#default_value' => !empty($form_state->getValue('availableitems')) ? $form_state->getValue('availableitems') : '',
        '#prefix' => '<div id="field-container"',
        '#suffix' => '</div>',
        '#ajax' => [
            'callback' => '::SecondDropdownCallback',
            'wrapper' => 'field-container-second',
            'event' => 'change'
        ]
    ];
    $form['secondavailableitems'] = [
        '#type' => 'select',
        '#title' => 'Models',
        '#options' =>static::secondavailableItems($cat2, $cat),
        '#default_value' => !empty($form_state->getValue('secondavailableitems')) ? $form_state->getValue('secondavailableitems') : '',
        '#prefix' => '<div id="field-container-second"',
        '#suffix' => '</div>'
    ];
    $form['thirdavailableitems'] = [
        '#type' => 'select',
        '#title' => 'Engines',
        '#options' =>static::thirdavailableItems($cat2),
        '#default_value' => !empty($form_state->getValue('thirdavailableitems')) ? $form_state->getValue('thirdavailableitems') : '',
        '#prefix' => '<div id="field-container-third"',
        '#suffix' => '</div>',
    ];
    $form['submit'] = [
        '#type' => 'submit',
        '#value' => 'Submit',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $trigger = (string) $form_state->getTriggeringElement()['#value'];
    if ($trigger != 'submit') {
        $form_state->setRebuild();
    }
  }

  public function DropdownCallback(array &$form, FormStateInterface $form_state) {
    return $form['availableitems'];
  }
  public function SecondDropdownCallback(array &$form, FormStateInterface $form_state) {
    return $form['secondavailableitems'];
  }
  public function ThirdDropdownCallback(array &$form, FormStateInterface $form_state) {
    return $form['thirdavailableItems'];
  }
 
  public function foodCategory() {
    $i = 2023;
    $years = [];
    for ($i=2023; $i >= 1995 ; $i--) { 
        $years[$i] = $i;
    }
   return $years;
  }

  public function availableItems($year) {
    $url = 'https://vpic.nhtsa.dot.gov/api/vehicles/GetMakesForManufacturerAndYear/mer?year='.$year.'&format=json';
    $data = $this->getJsonContent($url, 'MakeId', 'MakeName');
    if(empty($data)) {
        $data = [''=>'-None-'];
    }
    return $data;
  }
  public function getJsonContent($url, $id, $name) {
    $response = file_get_contents($url);
    $cat_facts = Json::decode($response);
    $data = [];
    foreach ($cat_facts as  $cat_fact) {
        foreach ($cat_fact as $item) {
           $data[$item[$id]] = $item[$name];
       }
    }
    
    return $data;
  }

  public function secondavailableItems($model_id, $year) {
    $url = 'https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMakeIdYear/makeId/'.$model_id.'/modelyear/'.$year.'?format=json';
    $data = $this->getJsonContent($url, 'Model_ID', 'Model_Name');
    if(empty($data)) {
        $data = [''=>'-None-'];
    }
    return $data;
  }

  public function thirdavailableItems($model_id) {
    // $url = 'https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMakeIdYear/makeId/'.$model_id.'/modelyear/'.$year.'?format=json';
    // $data = $this->getJsonContent($url, 'Model_ID', 'Model_Name');
    
    // return $data;
    return [''=>'-None-'];
  }


}