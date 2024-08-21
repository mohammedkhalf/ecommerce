<input id="yes" type='radio' name='cowpay_settings[fees]' value='1' <?php checked($options['fees'], 1) ?> /><label for="yes">Yes</label>
<input id="no" type='radio' name='cowpay_settings[fees]' value='2' <?php checked($options['fees'], 2) ?> /><label for="no">No</label>
<p><?php esc_html_e('Please Confrim fees on customer or not', 'cowpay') ?></p>