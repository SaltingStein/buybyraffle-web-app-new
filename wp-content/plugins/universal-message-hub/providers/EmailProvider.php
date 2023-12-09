<?php
/**
 * Email Provider Class File
 *
 * Implements IMessageProvider interface for sending emails.
 * Utilizes WordPress's wp_mail function for email delivery.
 */

// Include the interface definition
require_once plugin_dir_path(__FILE__) . '../includes/IMessageProvider.php';

/**
 * Class EmailProvider
 *
 * This class handles the email sending functionality for the Universal Message Hub plugin.
 * It uses WordPress's built-in wp_mail function to send emails.
 */
class EmailProvider implements IMessageProvider {

    /**
     * Send an email message.
     *
     * @param string $recipient The email address of the recipient.
     * @param array $message An associative array containing 'subject' and 'body' of the email.
     * @return bool Returns true on successful email delivery, false otherwise.
     * @throws Exception Throws an exception if email sending fails.
     */
    public function sendMessage($recipient, $message) {
        try {
            // Set email headers, specifying content type as HTML
            $headers = array('Content-Type: text/html; charset=UTF-8');

            // Send the email using WordPress's wp_mail function
            $sent = wp_mail($recipient, $message['subject'], $message['body'], $headers);

            // Check if the email was sent successfully
            if (!$sent) {
                // Throw an exception if email sending failed
                throw new Exception('Failed to send email.');
            }

            // Return true indicating successful email delivery
            return true;

        } catch (Exception $e) {
            // Log the error details in case of an exception
            umh_log_error('EmailProvider Error: ' . $e->getMessage(), [
                'recipient' => $recipient,
                'message' => $message
            ]);

            // Rethrow the exception to be handled by the caller
            throw $e;
        }
    }
}
