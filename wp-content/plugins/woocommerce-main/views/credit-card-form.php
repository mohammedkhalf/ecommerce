<p class="form-row cowpay_credit_card_number validate-required validate_cowpay_credit_card_number form-row-wide" 
    id="cowpay_credit_card_number_field" data-priority="90" 
    data-o_class="form-row form-row-wide cowpay_credit_card_number validate-required validate-postcode">

        <label for="cowpay_credit_card_number" class=""><?=esc_html__('Card Number', 'cowpay')?>&nbsp;<abbr class="required" title="required">*</abbr></label>
        <span class="woocommerce-input-wrapper">
            <input type="text" class="input-text " name="cowpay_credit_card_number" id="cowpay_credit_card_number" placeholder="" value="" autocomplete="cowpay_credit_card_number"  style="width:400px;height:20px">
        </span>
</p>


<p class="form-row cowpay_credit_card_expire_month_field validate-required validate_cowpay_credit_card_expire_month form-row-wide" 
    id="cowpay_credit_card_expire_month_field" data-priority="90" 
    data-o_class="form-row form-row-wide cowpay_credit_card_expire_month_field validate-required validate_cowpay_credit_card_expire_month_field">

    <label for="cowpay_credit_card_expire_month" class="">Expire Month&nbsp;<abbr class="required" title="required">*</abbr></label>
    <span class="woocommerce-input-wrapper">
        
        <select id="cowpay_credit_card_expire_month" name="cowpay_credit_card_expire_month" class="form-row cowpay_credit_card_expire_month_field validate-required validate_cowpay_credit_card_expire_month form-row-wide" inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" maxlength="2" style="width:400px;height:20px">
           <option value="01"> 01 </option>
           <option value="02"> 02 </option>
           <option value="03"> 03 </option>
           <option value="04"> 04 </option>
           <option value="05"> 05 </option>
           <option value="06"> 06 </option>
           <option value="07"> 07 </option>
           <option value="08"> 08 </option>
           <option value="09"> 09 </option>
           <option value="10"> 10 </option>
           <option value="11"> 11 </option>
           <option value="1"> 12 </option>
    
    </select>
    </span>
</p>

<p class="form-row cowpay_credit_card_expire_month_field validate-required validate_cowpay_credit_card_expire_month form-row-wide" 
    id="cowpay_credit_card_expire_month_field" data-priority="90" 
    data-o_class="form-row form-row-wide cowpay_credit_card_expire_month_field validate-required validate_cowpay_credit_card_expire_month_field">

    <label for="cowpay_credit_card_expire_month" class="">Expire Year&nbsp;<abbr class="required" title="required">*</abbr></label>
    <span class="woocommerce-input-wrapper">
        
        <select id="cowpay_credit_card_expire_year" name="cowpay_credit_card_expire_year" class="form-row cowpay_credit_card_expire_year_field validate-required validate_cowpay_credit_card_expire_year form-row-wide" inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" maxlength="2" style="width:400px;height:20px">
    
        <?php for ($i = 0; $i <= 10; $i++) { ?>
            <option value="<?php echo date('y', strtotime('+' . $i . ' year')) ?>"> <?php echo date('y', strtotime('+' . $i . ' year')) ?></option>;
        <?php } ?>
    
    </select>
    </span>
</p>

<p class="form-row cowpay_credit_card_cvv_field validate-required cowpay_credit_card_cvv form-row-wide" 
    id="cowpay_credit_card_cvv_field" data-priority="90" 
    data-o_class="form-row form-row-wide cowpay_credit_card_cvv_field validate-required validate_cowpay_credit_card_cvv_field">

    <label for="cowpay_credit_card_cvv" class="">cvv&nbsp;<abbr class="required" title="required">*</abbr></label>
    <span class="woocommerce-input-wrapper">
        <input type="password" class="input-text " name="cowpay_credit_card_cvv" id="cowpay_credit_card_cvv" placeholder="" value="" autocomplete="cowpay_credit_card_cvv" style="width:400px;height:20px"> 
    </span>
</p>
