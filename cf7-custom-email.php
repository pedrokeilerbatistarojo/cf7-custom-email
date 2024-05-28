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

    $eliEmail = 'kattia@elischools.com';
    $iehEmail = 'hello@irelandemploymenthub.com';

    $form_id = $contact_form->id();

    $submission = WPCF7_Submission::get_instance();

    if ($submission) {

        // Obtain Form Data
        $posted_data = $submission->get_posted_data();

        $alternative_email = '';

        //Log form id
        error_log("Processing form ID: " . $form_id);

        // Defined Rules
        if ($form_id == '3459'){

            $location = $posted_data['your-location'] ?? '';
            $type = $posted_data['your-type'] ?? '';

            // Cast to array and join the array elements with a comma.
            $location = implode(', ', (array) $location);
            $type = implode(', ', (array) $type);

            error_log("Location: " . $location);
            error_log("Type: " . $type);

            error_log("Location: " . $location);
            error_log("Type: " . $type);

            /*
             * Case 3:
             * If you mark that you are not in Ireland,
             * please contact me at mayra@elischools.com
             */

            if ($location === 'No'){

                $alternative_email = $eliEmail;

            }else if ($location === 'Yes'){

                /*
                 * Case 1:
                 * If you click YES you are in Ireland and YES you are an ELI or WST student,
                 * you must go to hello@irelandemploymenthub.com
                 */

                if ($type === 'Yes'){
                    $alternative_email = $iehEmail;
                }

                /*
                 * Case 2:
                 * If you click that you are in Ireland but are not an ELI or WST student,
                 * please contact me at mayra@elischools.com
                 */

                if ($type === 'No'){
                    $alternative_email = $eliEmail;
                }
            }
        }

        if (!empty($alternative_email)) {
            $mail = $contact_form->prop('mail');
            $mail['recipient'] = $alternative_email;

            // Save the changes
            $contact_form->set_properties(['mail' => $mail]);

            error_log("Email routed to: " . $alternative_email);
        }else{
            error_log('Could not determine alternate email address for form with ID:' . $form_id);
        }
    }else{
        error_log('Could not get the form submit instance for the form with ID:' . $form_id);
    }
}
