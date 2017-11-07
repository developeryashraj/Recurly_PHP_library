<?php echo $this->Html->script('plan'); ?>
<div class="padding-block plan-billing-detail">
    <div class="container">
        <div class="row">
            <div class="alert alert_plan alert-success alert-dismissible fade in" role="alert" style="display: none;">
                <button type="button" class="close custom_close" aria-label="Close"><span aria-hidden="true">×</span></button>
                <span id="message-plan"></span>
            </div>
            <div class="col-lg-8 col-md-10 col-sm-12 col-xs-12 col-lg-push-2 col-lg-pull-2 col-sm-push-0 col-sm-pull-0 col-md-push-1 col-md-pull-1">
                <?php echo $this->Form->create('BillingInfo', array('class' => 'form_validation_reg', 'type' => 'post', 'action' => 'save', 'id' => 'billing_details')); ?>
                <!--pricing page title -->
                <?php
                if (!empty($plan) && $plan == 'enterprise') {
                    ?>
                    <h1 class="pagetitle block-title"><?php echo $GLOBALS['LABELS']['MODULES']['SelectPlan']['header']; ?></h1>
                <?php } ?>
                <!--pricing page title -->
                <!--card-bucket -->
                <div class="card card-bucket z-depth">

                    <?php if (!empty($plan)) { ?>
                        <div class="card-header">
                            <span class="bucket-title"><?php echo str_replace('#IDENTIFIER#', ucfirst($plan), $GLOBALS['LABELS']['MODULES']['SelectPlan']['identifier_plan']); ?></span>
                        </div>
                    <?php } ?>

                    <div class="card-body">
                        <?php
                        if (!empty($plan) && $plan == 'enterprise') {
                            ?>
                            <!--bucket-steps -->
                            <div class="bucket-steps">
                                <h3><?php echo $GLOBALS['LABELS']['MODULES']['SelectBucket']['select_bucket']; ?></h3>

                                <div class="media quantity-step">
                                    <div class="media-body media-middle">
                                        <p><?php echo $GLOBALS['LABELS']['MODULES']['SelectBucket']['bucket_help_text']; ?></p>
                                    </div>
                                    <div class="media-right media-middle">
                                        <div class="quantity-box">
                                            <button type="button" class="btn btn-quantity btn-down ripple-effect"></button>
                                            <input type="text" name="quantity" value="1" class="form-control" placeholder="" id="planBucketQuantity"/>
                                            <button type="button" class="btn btn-quantity btn-up  ripple-effect"></button>
                                        </div>
                                    </div>        
                                </div>
                            </div> 
                            <!--bucket-steps -->
                        <?php } ?>
                        <!--bucket-steps -->
                        <div class="bucket-steps">
                            <h3><?php
                                $text = $GLOBALS['LABELS']['MODULES']['SelectPlan']['select_plan'];
                                if (!empty($plan) && $plan == 'enterprise') {
                                    $text = str_replace("1", "2", $GLOBALS['LABELS']['MODULES']['SelectPlan']['select_plan']);
                                }
                                echo $text;
                                ?></h3>
                            <!--radio -->
                            <span class="radio">
                                <label class="radio-options ripple-effect">
                                    <input type="radio" name="plan" id="annual" checked value="<?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['Annually']; ?>">
                                    <label for="annual" class="media">
                                        <div class="media-body media-middle">
                                            <strong><?php echo $GLOBALS['LABELS']['MODULES']['SelectPlan']['annual_billing']; ?></strong>

                                            <p><?php echo str_replace("#PLANRATE", !empty($planDetail[0]['PlanMaster']['plan_rate']) ? number_format($planDetail[0]['PlanMaster']['plan_rate']) : 0, $GLOBALS['LABELS']['MODULES']['SelectPlan']['per_year_planrate']);
                                ?></p>
                                        </div>

                                        <div class="media-right media-middle">
                                            <strong class="plan-price">$<?php echo!empty($planDetail[0]['PlanMaster']['plan_rate']) ? number_format($planDetail[0]['PlanMaster']['plan_rate']) : 0; ?></strong>
                                            <input type="hidden" id="dbPlanCode" value="<?php echo $planDetail[0]['PlanMaster']['plan_code']; ?>">
                                            <input type="hidden" id="dbPlanId" value="<?php echo $planDetail[0]['PlanMaster']['id']; ?>">
                                            <span class="plan-detail"><?php echo $GLOBALS['LABELS']['MODULES']['SelectPlan']['per_year_text']; ?></span>
                                        </div>
                                    </label>
                                </label>
                            </span>
                            <!--radio -->
                            <div class="clearfix"></div>	
                            <!--radio -->
                            <span class="radio">
                                <label class="radio-options ripple-effect">
                                    <input type="radio" name="plan" id="monthly" checked value="<?php echo $GLOBALS['LABELS']['MODULES']['BillingSummary']['Monthly']; ?>">
                                    <label for="monthly" class="media">
                                        <div class="media-body media-middle">
                                            <strong><?php echo $GLOBALS['LABELS']['MODULES']['SelectPlan']['monthly_billing']; ?></strong>
                                            <p><?php echo str_replace("#PLANRATE", !empty($planDetail[1]['PlanMaster']['plan_rate']) ? number_format($planDetail[1]['PlanMaster']['plan_rate']) : 0, $GLOBALS['LABELS']['MODULES']['SelectPlan']['per_month_planrate']); ?></p>
                                        </div>
                                        <div class="media-right media-middle">
                                            <strong class="plan-price">$<?php echo!empty($planDetail[1]['PlanMaster']['plan_rate']) ? number_format($planDetail[1]['PlanMaster']['plan_rate']) : 0; ?></strong>
                                            <input type="hidden" id="dbPlanCode" value="<?php echo $planDetail[1]['PlanMaster']['plan_code']; ?>">
                                            <input type="hidden" id="dbPlanId" value="<?php echo $planDetail[1]['PlanMaster']['id']; ?>">
                                            <span class="plan-detail"><?php echo $GLOBALS['LABELS']['MODULES']['SelectPlan']['per_month_text']; ?></span>
                                        </div>
                                    </label>
                                </label>
                            </span>
                            <!--radio -->
                        </div>
                        <!--bucket-steps -->

                    </div>
                </div>
                <!--card-bucket -->

                <!--pricing page title -->
                <h1 class="pagetitle block-title"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['header']; ?></h1>
                <!--pricing page title -->
                <!--card-bucket -->
                <div class="card payment-card-form z-depth orange">

                    <div class="row">
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group paper-input floating-label">
                                <label class="control-label" for="First-Name"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['company']; ?></label>
                                <?php
                                if (!empty($billDetail['company'])) {
                                    ?>
                                    <input type="text" name="company" class="form-control" value="<?php echo $billDetail['company']; ?>">
                                <?php } else { ?>
                                    <input type="text" name="company" class="form-control" value="<?php echo!empty($GLOBALS['user_info']['EntityTypeMaster']['name']) ? $GLOBALS['user_info']['EntityTypeMaster']['name'] : ''; ?>">
                                <?php } ?>
                                <span class="help-text"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['company_help_text']; ?></span>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group paper-input floating-label">
                                <label class="control-label" for="First-Name"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['name']; ?></label>
                                <?php
                                if (!empty($billDetail['fullname'])) {
                                    ?>
                                    <input type="text" name="fullname" class="form-control" value="<?php echo $billDetail['fullname']; ?>">
                                <?php } else { ?>
                                    <input type="text" name="fullname" class="form-control" value="<?php echo!empty($GLOBALS['user_info']['PersonMaster']['first_name']) ? $GLOBALS['user_info']['PersonMaster']['first_name'] . " " . $GLOBALS['user_info']['PersonMaster']['last_name'] : ''; ?>">
                                <?php } ?>

                                <span class="help-text"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['name_help_text']; ?></span>
                            </div>
                        </div>

                        <div class="col-sm-12 col-xs-12">
                            <div class="form-group paper-input floating-label">
                                <label class="control-label" for="First-Name"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['email']; ?></label>
                                <select class="select-add-tags" name="email[]" multiple>
                                    <?php
                                    if (!empty($billDetail['email'])) {
                                        foreach ($billDetail['email'] as $oneEmail) {
                                        ?>
                                        <option selected="true" value="<?php echo $oneEmail; ?>"> <?php echo $oneEmail; ?></option>
                                        <?php } ?>
                                    <?php } else if (!empty($GLOBALS['user_info']['PersonMaster']['email1'])) { ?>
                                        <option selected="true" value="<?php echo $GLOBALS['user_info']['PersonMaster']['email1']; ?>"> <?php echo $GLOBALS['user_info']['PersonMaster']['email1']; ?></option>
                                    <?php } ?>
                                </select>
                                <span class="help-text"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['email_help_text']; ?></span>
                            </div>
                        </div>

                        <div class="col-sm-12 col-xs-12">
                            <div class="form-group paper-input floating-label">
                                <label class="control-label" for="First-Name"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['address']; ?></label>
                                <input type="text" name="address" class="form-control" value="<?php echo!empty($billDetail['address']) ? $billDetail['address'] : ''; ?>">
                                <span class="help-text"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['address_help_text']; ?></span>
                            </div>
                        </div>

                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group paper-input floating-label">
                                <label class="control-label" for="First-Name">Country*</label>
                                <select class="select-with-search" name="country">
                                    <option value=""></option>
                                    <?php if (!empty($countryMaster)) { ?>
                                        <?php foreach ($countryMaster as $key => $value) { ?>
                                            <option value="<?php echo $key; ?>" <?php if (!empty($billDetail['country']) && $billDetail['country'] == $key) { ?> selected="selected"<?php } ?>><?php echo $value; ?></option>
                                        <?php } ?>
                                    <?php } ?>

                                </select>
                                <span class="help-text"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['country_help_text']; ?></span>
                            </div>
                        </div>

                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group paper-input floating-label">
                                <label class="control-label" for="First-Name"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['state']; ?></label>
                                <select class="select-with-search" name="state">
                                    <option></option>
                                    <?php if (!empty($stateMaster)) { ?>
                                        <?php foreach ($stateMaster as $key => $value) { ?>
                                            <option value="<?php echo $key; ?>" <?php if (!empty($billDetail['state']) && $billDetail['state'] == $key) { ?> selected="selected"<?php } ?>><?php echo $value; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                                <span class="help-text"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['state_help_text']; ?></span>
                            </div>
                        </div>

                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group paper-input floating-label">
                                <label class="control-label" for="First-Name"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['city']; ?></label>
                                <select class="select-with-search" name="city">
                                    <option></option>
                                    <?php if (!empty($cityMaster)) { ?>
                                        <?php foreach ($cityMaster as $key => $value) { ?>
                                            <option value="<?php echo $key; ?>" <?php if (!empty($billDetail['city']) && $billDetail['city'] == $key) { ?> selected="selected"<?php } ?>><?php echo $value; ?></option>
                                        <?php } ?>
                                    <?php } ?>                               	
                                </select>
                                <span class="help-text"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['city_help_text']; ?></span>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group paper-input floating-label">
                                <label class="control-label" for="First-Name"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['zipcode']; ?></label>
                                <input type="text" name="zipcode" class="form-control" value="<?php echo!empty($billDetail['zipcode']) ? $billDetail['zipcode'] : ''; ?>">
                                <span class="help-text"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['zipcode_help_text']; ?></span>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group paper-input floating-label floating-label-completed">
                                <label class="control-label" for="First-Name"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['currency']; ?></label>
                                <select class="select-with-search" name="currency">
                                    <option></option>
                                    <option value="USD" selected="selected">USD</option>
                                </select>
                                <span class="help-text"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['currency_help_text']; ?></span>
                            </div>
                        </div>

                    </div>    
                </div>
                <!--card-bucket -->
                <input type="hidden" name="plan_code" id="planCode" value="<?php echo!empty($billDetail['plan_code']) ? $billDetail['plan_code'] : ''; ?>">
                <input type="hidden" name="plan_amount" id="planAmount" value="<?php echo!empty($billDetail['plan_amount']) ? $billDetail['plan_amount'] : ''; ?>">
                <input type="hidden" name="plan_id" id="planId" value="<?php echo!empty($billDetail['plan_id']) ? $billDetail['plan_id'] : ''; ?>">

                <div class="text-center payment-action orange">
                    <a class="btn btn-default ripple-effect" href="<?php echo SITE_URL . 'users/plans'; ?>"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['back_btn']; ?></a>
                    <button class="btn btn-orange ripple-effect" type="submit"><?php echo $GLOBALS['LABELS']['MODULES']['BillingDetails']['save_btn']; ?></button>
                </div>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </div>
</div>