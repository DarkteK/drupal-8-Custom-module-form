<?php
namespace Drupal\custom_forms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

use Drupal\Core\Ajax\InvokeCommand;

use Drupal\custom_api_clients\CustomApiClientExtension;


/**
 * Class LaunchCheckoutForm.
 */
class BenefitsRequestForm extends FormBase {
  
  const STATES = [
    'AL' => 'Alabama',
    'AK' => 'Alaska',
    'AZ' => 'Arizona',
    'AR' => 'Arkansas',
    'CA' => 'California',
    'CO' => 'Colorado',
    'CT' => 'Connecticut',
    'DE' => 'Delaware',
    'DC' => 'District of Columbia',
    'FL' => 'Florida',
    'GA' => 'Georgia',
    'HI' => 'Hawaii',
    'ID' => 'Idaho',
    'IL' => 'Illinois',
    'IN' => 'Indiana',
    'IA' => 'Iowa',
    'KS' => 'Kansas',
    'KY' => 'Kentucky',
    'LA' => 'Louisiana',
    'ME' => 'Maine',
    'MD' => 'Maryland',
    'MA' => 'Massachusetts',
    'MI' => 'Michigan',
    'MN' => 'Minnesota',
    'MS' => 'Mississippi',
    'MO' => 'Missouri',
    'MT' => 'Montana',
    'NE' => 'Nebraska',
    'NV' => 'Nevada',
    'NH' => 'New Hampshire',
    'NJ' => 'New Jersey',
    'NM' => 'New Mexico',
    'NY' => 'New York',
    'NC' => 'North Carolina',
    'ND' => 'North Dakota',
    'OH' => 'Ohio',
    'OK' => 'Oklahoma',
    'OR' => 'Oregon',
    'PA' => 'Pennsylvania',
    'RI' => 'Rhode Island',
    'SC' => 'South Carolina',
    'SD' => 'South Dakota',
    'TN' => 'Tennessee',
    'TX' => 'Texas',
    'UT' => 'Utah',
    'VT' => 'Vermont',
    'VA' => 'Virginia',
    'WA' => 'Washington',
    'WV' => 'West Virginia',
    'WI' => 'Wisconsin',
    'WY' => 'Wyoming',
  ];


  const PROVINCES = [
    'AB' => 'Alberta',
    'BC' => 'British Columbia',
    'MB' => 'Manitoba',
    'NB' => 'New Brunswick',
    'NL' => 'Newfoundland and Labrador',
    'NS' => 'Nova Scotia',
    'NT' => 'Northwest Territories',
    'NU' => 'Nunavut',
    'ON' => 'Ontario',
    'PE' => 'Prince Edward Island',
    'QC' => 'Quebec',
    'SK' => 'Saskatchewan',
    'YT' => 'Yukon',
  ];
  const PRODUCTS = [
    'LEGALSHIELD & IDSHIELD' => 'BOTH',
    'LEGALSHIELD' => 'LEGALSHIELD',
    'IDSHIELD' => 'IDSHIELD',
  ];

  const TIME_CONTACT = [
    '8AM - 11AM' => '8AM - 11AM',
    '11AM - 2PM' => '11AM - 2PM',
    '2PM - 5PM' => '2PM - 5PM',
  ];

  /**
   * Drupal\custom_associates\AssociateAffiliationServiceInterface definition.
   *
   * @var \Drupal\custom_associates\AssociateAffiliationServiceInterface
   */
  protected static $assocAffiliation;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
   
    // Instantiates this form class.
    $instance = new static(
      // Load the service required to construct this class.
      $container->get('current_user')
    );

    self::$assocAffiliation = $container->get('custom_associates.affiliation');

    return $instance;
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'benefits_request_form';
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#theme'] = 'benefits_request_form_block';

    $form['#attached']['library'][] = 'custom_forms/custom-forms_benefits-request-form';
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/jquery';    

    $form['#attributes']['novalidate'] = 'novalidate';

    try {
      $id = self::$assocAffiliation->getIdAssociated();
      $domain_full = \Drupal::request()->getSchemeAndHttpHost();

    } catch (\Throwable $th) {
      // Error retrieve Affiliation Associated data
      return [];
    }

    $form['associated_id'] = [
      '#type' => 'hidden',
      '#value' => $id
    ];
    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => t('FIRST NAME'),
    ];
    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => t('LAST NAME'),
    ];
    $form['city']= [
      '#type' => 'textfield',
      '#title' => t('CITY'),
    ];

    if(strpos($domain_full, '.com') !== false){
      $form['state']= [
        '#type' => 'select',
        '#title' => t('STATE'),
        '#options' => self::STATES,
      ];

    }else{

      $form['state']= [
        '#type' => 'select',
        '#title' => t('PROVINCE'),
        '#options' => self::PROVINCES,
      ];
    }

    $form['company_name'] = [
      '#type' => 'textfield',
      '#title' => t('COMPANY NAME'),      
    ];
    $form['number_employees'] = [
      '#type' => 'number',
      '#title' => t('NUMBER OF EMPLOYEES'),            
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => t('EMAIL'),
    ];
    $form['phone_number'] = [
      '#type' => 'tel',
      '#title' => t('PHONE NUMBER'),     
    ];
    $form['product']= [
      '#type' => 'select',
      '#title' => t('PRODUCT(S) YOU ARE INTERESTED IN'),  
      '#options' => self::PRODUCTS,
    ];
    $form['time']= [
      '#type' => 'select',
      '#title' => t('BEST TIME TO CONTACT'),  
      '#options' => self::TIME_CONTACT,
    ];
    $form['actions'] = [
      '#type' => 'button',
      '#value' => $this->t('Submit'),
      '#ajax' => [
        'callback' => '::submitForm',
      ],
    ];    
    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $ajax_response = new AjaxResponse();

    if (! $this->submitValidateForm($form, $form_state, $ajax_response)){
      $id = $form_state->getValue('associated_id');
      $data = [
        'name'=> $form_state->getValue('first_name') . ' ' . $form_state->getValue('last_name'),
        'city'=> $form_state->getValue('city'),
        'region'=> $form_state->getValue('state'),
        'phone_number'=> trim($form_state->getValue('phone_number'))==''?0:preg_replace('/[^\d]/', '', $form_state->getValue('phone_number')),
        'email'=> $form_state->getValue('email'),
        'comments'=> 
          'Company Name: ' . $form_state->getValue('company_name') . '\n' . 
          'Best time to contact: ' . $form_state->getValue('time') . '\n' .
          'Number of employees: ' . $form_state->getValue('number_employees') . '\n' .
          'Product of Interest: ' . $form_state->getValue('product'),
        'is_corporate_lead'=> false,
        'type'=> 'M',
      ];

      $clientWrapper = new CustomApiClientExtension();
      $result = $clientWrapper->sendLicenseForm($data, $id);

      $ajax_response->addCommand(new InvokeCommand('#benefits-request-form','trigger', ['reset']));
      $ajax_response->addCommand(new InvokeCommand('#block-benefits_request_form-dialog','show', []));
    }

    return $ajax_response;

  }  


  /**
   * {@inheritdoc}
   */
  public function submitValidateForm(array &$form, FormStateInterface $form_state, AjaxResponse &$ajax_response) {

    $error = false;

    if (strlen(trim($form_state->getValue('first_name'))) < 1) {
      $error = true;
      $ajax_response->addCommand(new HtmlCommand('#inline-error_edit-first-name', 'First Name must have a value.'));
    }
    else {
      $ajax_response->addCommand(new HtmlCommand('#inline-error_edit-first-name', ''));      
    }
    
    if (strlen(trim($form_state->getValue('last_name'))) < 1) {
      $error = true;
      $ajax_response->addCommand(new HtmlCommand('#inline-error_edit-last-name', 'Last Name must have a value.'));
    }
    else {
      $ajax_response->addCommand(new HtmlCommand('#inline-error_edit-last-name', ''));      
    }

    if (strlen(trim($form_state->getValue('city'))) < 1) {
      $error = true;
      $ajax_response->addCommand(new HtmlCommand('#inline-error_edit-city', 'City must have a value.'));
    }
    else {
      $ajax_response->addCommand(new HtmlCommand('#inline-error_edit-city', ''));      
    }

    if (strlen(trim($form_state->getValue('company_name'))) < 1) {
      $error = true;
      $ajax_response->addCommand(new HtmlCommand('#inline-error_edit-company-name', 'Company Name must have a value.'));
    }
    else {
      $ajax_response->addCommand(new HtmlCommand('#inline-error_edit-company-name', ''));      
    }

    if (strlen(trim($form_state->getValue('email'))) < 1) {
      $error = true;
      $ajax_response->addCommand(new HtmlCommand('#inline-error_edit-email', 'Email must have a value.'));
    }
    else {
      $ajax_response->addCommand(new HtmlCommand('#inline-error_edit-email', ''));      
    }

    if (strlen(trim($form_state->getValue('phone_number'))) < 1) {
      $error = true;
      $ajax_response->addCommand(new HtmlCommand('#inline-error_edit-phone-number', 'Phone Number must have a value.'));
    }
    else {
      $ajax_response->addCommand(new HtmlCommand('#inline-error_edit-phone-number', ''));      
    }

    return $error;    
  }    

}
