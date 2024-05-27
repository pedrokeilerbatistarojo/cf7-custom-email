<?php

/*
Plugin Name: CF7 Custom Email Routing
Plugin URI: https://github.com/pedrokeilerbatistarojo/cf7-custom-email
Description: Changes the destination email address based on a field selection in various Contact Form 7 forms.
Version: 1.0
Author: Pedro Keiler Batista Rojo <pedrokeilerbatistarojo@gmail.com>
Author URI: https://github.com/pedrokeilerbatistarojo/
*/


// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

add_action('wpcf7_before_send_mail', 'cf7_custom_email_routing');


function cf7_custom_email_routing($contact_form): void
{

    $form_id = $contact_form->id();

    $submission = WPCF7_Submission::get_instance();

    if ($submission) {

        // Obtain Form Data
        $posted_data = $submission->get_posted_data();

        $alternative_email = '';

        // Defined Rules
        if ($form_id === '2ab79a7'){
            $location = $posted_data['your-location'] ?? '';
            $type = $posted_data['your-type'] ?? '';

            /*
             * Case 1:
             * If you mark that you are not in Ireland,
             * please contact me at mayra@elischools.com
             */

            if ($location === 'No'){

                $alternative_email = 'mayra@elischools.com';

            }else if ($location === 'Yes'){

                /*
                 * Case 2:
                 * If you click YES you are in Ireland and YES you are an ELI or WST student,
                 * you must go to hello@irelandemploymenthub.com
                 */

                if ($type === 'Yes'){
                    $alternative_email = 'hello@irelandemploymenthub.com';
                }

                /*
                 * Case 3:
                 * If you click that you are in Ireland but are not an ELI or WST student,
                 * please contact me at mayra@elischools.com
                 */

                if ($type === 'No'){
                    $alternative_email = 'mayra@elischools.com';
                }

            }
        }

        if (!empty($alternative_email)) {
            $mail = $contact_form->prop('mail');
            $mail['recipient'] = $alternative_email;

            // Save the changes
            $contact_form->set_properties(['mail' => $mail]);
        }else{
            error_log('Could not determine alternate email address for form with ID:' . $form_id);
        }
    }else{
        error_log('Could not get the form submit instance for the form with ID:' . $form_id);
    }
}
