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

add_action('admin_menu', 'cf7_custom_email_routing_menu');
add_action('admin_init', 'cf7_custom_email_routing_settings');

function cf7_custom_email_routing_menu() {
    add_options_page(
        'CF7 Custom Email Routing Settings',
        'CF7 Email Routing',
        'manage_options',
        'cf7-email-routing',
        'cf7_custom_email_routing_settings_page'
    );
}

function cf7_custom_email_routing_settings() {
    register_setting('cf7_email_routing_options', 'cf7_email_routing_forms');

    add_settings_section(
        'cf7_email_routing_main_section',
        'Email Routing Settings',
        'cf7_email_routing_section_callback',
        'cf7-email-routing'
    );

    add_settings_field(
        'cf7_email_routing_forms',
        'Form Email Routing',
        'cf7_email_routing_forms_callback',
        'cf7-email-routing',
        'cf7_email_routing_main_section'
    );
}

function cf7_email_routing_section_callback() {
    echo '<p>Configure the form ID and email routing options below:</p>';
}

function cf7_email_routing_forms_callback() {
    $options = get_option('cf7_email_routing_forms');
    echo '<textarea id="cf7_email_routing_forms" name="cf7_email_routing_forms" rows="10" cols="50" class="large-text code">' . esc_textarea($options) . '</textarea>';
    echo '<p>Enter the form ID, ELI email, and IEH email in the following format (one per line):</p>';
    echo '<pre>form_id,eli_email,ieh_email</pre>';
}

function cf7_custom_email_routing_settings_page() {
    ?>
    <div class="wrap">
        <h1>CF7 Custom Email Routing Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cf7_email_routing_options');
            do_settings_sections('cf7-email-routing');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('wpcf7_before_send_mail', 'cf7_custom_email_routing');

function cf7_custom_email_routing($contact_form): void
{
    $form_id = $contact_form->id();
    $submission = WPCF7_Submission::get_instance();

    if ($submission) {
        $posted_data = $submission->get_posted_data();
        $alternative_email = '';

        // Log form id
        error_log("Processing form ID: " . $form_id);

        // Retrieve the routing settings
        $routing_settings = get_option('cf7_email_routing_forms');
        $routing_lines = explode("\n", $routing_settings);

        foreach ($routing_lines as $line) {
            list($configured_form_id, $eli_email, $ieh_email) = array_map('trim', explode(',', $line));

            if ($form_id == $configured_form_id) {
                $location = $posted_data['your-location'] ?? '';
                $type = $posted_data['your-type'] ?? '';

                // Cast to array and join the array elements with a comma
                $location = implode(', ', (array)$location);
                $type = implode(', ', (array)$type);

                error_log("Location: " . $location);
                error_log("Type: " . $type);

                if ($location === 'No') {
                    $alternative_email = $eli_email;
                } elseif ($location === 'Yes') {
                    if ($type === 'Yes') {
                        $alternative_email = $ieh_email;
                    } elseif ($type === 'No') {
                        $alternative_email = $eli_email;
                    }
                }
                break;
            }
        }

        if (!empty($alternative_email)) {
            $mail = $contact_form->prop('mail');
            $mail['recipient'] = $alternative_email;
            $contact_form->set_properties(['mail' => $mail]);

            error_log("Email routed to: " . $alternative_email);
        } else {
            error_log('Could not determine alternate email address for form with ID:' . $form_id);
        }
    } else {
        error_log('Could not get the form submit instance for the form with ID:' . $form_id);
    }
}
