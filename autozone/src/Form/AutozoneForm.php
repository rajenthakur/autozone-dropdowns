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
    // $url = "https://vpic.nhtsa.dot.gov/api/vehicles/GetAllMakes?format=json"
    //     $request = file_get_contents($uri);
    //     $makes_list = json::decode($request->getBody()->getContents());
    //     print_r($makes_list);die("test");


    //     try{

    //         $client = new Client([
    //             'headers' => [
    //                 'Accept' => 'application/json',
    //                 'Content-Type' => 'application/json',
    //              ],
    //             "base_uri"=> $this->base_uri,
    //             'connect_timeout' => 10
    //         ]);

    //     $response = $client->request(
    //                     "GET",
    //                    "https://vpic.nhtsa.dot.gov/api/vehicles/GetAllMakes?format=json", 
    //                     );
    //     $abc = json_decode($response->getBody()->getContents(), true);
    //     print_r($abc);die("sssssssssssss");
    // }

    // catch(RequestException $ex){
    //     $apiResponse = json_decode($response->getBody()->getContents(), true);
    //     return $apiResponse['message'];
    // }



// $url = "https://vpic.nhtsa.dot.gov/api/vehicles/GetAllMakes?format=json&modelyear=2011";

// $curl = curl_init($url);
// curl_setopt($curl, CURLOPT_URL, $url);
// curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// $headers = array(
//    "Accept: application/json",
// );
// curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
// //for debug only!
// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

// $resp = curl_exec($curl);
// curl_close($curl);
// var_dump($resp);




    $opt = static::foodYears();
    
    if (empty($form_state->getValue('year'))) {
        $cat = "none";
    }
    else {
        $cat = $form_state->getValue('Year');
    }
    if (empty($form_state->getValue('makes'))) {
        $cat2 = "none";
    }
    else {
        $cat2 = $form_state->getValue('makes');
    }
    if (empty($form_state->getValue('model'))) {
        $cat3 = "none";
    }
    else {
        $cat3 = $form_state->getValue('model');
    }

    $form['year'] = [
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
    $form['makes'] = [
        '#type' => 'select',
        '#title' => 'Available Makes',
        '#options' =>static::availableMake($cat),
        '#default_value' => !empty($form_state->getValue('makes')) ? $form_state->getValue('makes') : '',
        '#prefix' => '<div id="field-container"',
        '#suffix' => '</div>',
        '#ajax' => [
            'callback' => '::SecondDropdownCallback',
            'wrapper' => 'field-container-second',
            'event' => 'change'
        ]
    ];
    $form['model'] = [
        '#type' => 'select',
        '#title' => 'Available Models',
        '#options' =>static::availableModel($cat2, $cat),
        '#default_value' => !empty($form_state->getValue('model')) ? $form_state->getValue('model') : '',
        '#prefix' => '<div id="field-container-second"',
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
    return $form['availablemakes'];
  }
  public function SecondDropdownCallback(array &$form, FormStateInterface $form_state) {
    return $form['availablemodel'];
  }
 
  
  public function foodYears() {
   
    $i = 2023;
    $years = [];
    for ($i=2023; $i >= 1995 ; $i--) { 
        $years[$i] = $i;
    }
   return $years;
  }

  public function availablemakes($year) {
    $url = 'https://vpic.nhtsa.dot.gov/api/vehicles/GetMakesForManufacturerAndYear/mer?year='.$year.'&format=json';
    $data = $this->getJsonContent($url, 'MakeId', 'MakeName');
    
    return $data;
  }
  public function getJsonContent($url, $id, $name) {
    $response = file_get_contents($url);
    $cat_facts = Json::decode($response);
    $data = [];
    foreach ($cat_facts as  $cat_fact) {
        $i = 0;
        foreach ($cat_fact as $item) {
            $i++;
            // print_r($item);die("ssssss");
            if($i != 50) {
                $data[$item[$id]] = $item[$name];
            } else {
               break;
            }
        }
    }
    
    return $data;
  }

  public function availablemodel($model_id, $year) {
    // $url = 'https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMakeId/'.$model_id.'?format=json';
    // $data = $this->getJsonContent($url, 'Make_Id', 'Make_Name');
    $url = 'https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMakeIdYear/makeId/'.$model_id.'/modelyear/'.$year.'?format=json';
    $data = $this->getJsonContent($url, 'Model_ID', 'Model_Name');
    
    return $data;
  }

  



}