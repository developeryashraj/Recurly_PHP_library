<div class="padding-block">
    <!--container -->
    <div class="container">
        <div class="alert alertRecurly alert-success alert-dismissible fade in" role="alert" style="display: none;">
            <button type="button" class="close custom_close" aria-label="Close"><span aria-hidden="true">×</span></button>
            <span id="message"></span>
        </div>
        NOTE :- Enable/disable or Show/Hide of quantity textbox as per plan type selection is remaining. Currently showing that only if current plan is enterprise.
        <div class="block portfolio portfolio-card blue">
            <!--row -->
            <div class="row">
                <!-- col-lg-9 -->
                <div class="col-lg-9 col-md-8 col-sm-8 col-xs-12">

                    <!--profile left intro section start -->
                    <div class="profile-left">
                        <!--row -->
                        <div class="row">
                            <!--company info card -->
                            <div class="col-sm-12 col-xs-12">
                                Your current plan is <strong><?php echo $currentPlan['PlanMaster']['plan_name'] . " $" . $currentPlan['PlanMaster']['plan_rate'] . $currentPlan['PlanMaster']['plan_subscription']; ?></strong>
                                <div class="clearfix"></div>
                                    <?php if($accessSubscription==1){ ?>
                                <a href="<?php echo SITE_URL . 'recurly/cancelSubscription'; ?>" class="cancel_subscription" onclick="return false;">
                                    Cancel My Subscription
                                </a>
                                <div class="clearfix"></div>
                                <?php echo $this->Form->create('UpdateSubscription', array('class' => 'form_validation_reg form-horizontal', 'type' => 'post', 'id' => 'update_subscription', 'onsubmit' => 'return false;')); ?>
                                <div class="row">
                                    <?php echo $this->Form->input('id', array('type' => 'radio', 'class' => 'input-large', 'options' => $plans, 'id' => 'type', 'legend' => false, 'separator' => '<br />')); ?>
                                    <?php /*foreach ($plans as $value) { ?>
                                        Change plan to <?php echo $value['PlanMaster']['plan_name'] . " $" . $value['PlanMaster']['plan_rate'] . $value['PlanMaster']['plan_subscription']; ?>
                                        <?php echo $this->Form->input('type', array('type' => 'radio', 'class' => 'input-large', 'options' => $GLOBALS['startup_types'], 'id' => 'type', 'legend' => false, 'separator' => '<br />')); ?>
                                        <?php //echo $this->Form->input('Plan.code', array('type' => 'radio', 'div' => false, 'label' => false, 'value' => $value['PlanMaster']['id'])); ?>
                                        <br/>
                                    <?php }*/ ?>
                                </div>
                                <?php if($currentPlan['PlanMaster']['plan_type']=='enterprise'){ 
                                    echo $this->Form->input('quantity', array('type' => 'text', 'class' => 'input-large', 'id' => 'quantity','value'=>$getCurrentSubscription['RecurlySubscription']['quantity'], 'div' => false, 'label' => 'Change number of users :-'));
                                    } ?>
                                <div class="form-group btns margin-bot-30">
                                    <div class="col-sm-8 col-lg-9 col-sm-offset-4 col-lg-offset-3">
                                        <button type="submit" class="btn btn-primary ripple-effect">Confirm</button>
                                    </div>
                                </div>
                                <?php echo $this->Form->end(); ?>
                                <div class="clearfix"></div>
                                <strong>Manage users</strong>
                                <?php echo $this->Form->create('AddUser', array('class' => 'form_validation_reg form-horizontal', 'type' => 'post', 'id' => 'add_user', 'onsubmit' => 'return false;')); ?>
                                <div class="row">
                                    <?php foreach ($colleagues as $singleData) { 
                                        $checked=false;
                                        if(in_array($singleData['UserMaster']['id'], $selectedUsers)){
                                            $checked=true;
                                        }
                                        $label = $singleData['PersonMaster']['first_name']." ".$singleData['PersonMaster']['last_name']." ( ".$singleData['UserMaster']['email_address']." )"; ?>
                                        <?php echo $this->Form->input('ids.', array('type' => 'checkbox','hiddenField'=>false,'div'=>false,'label'=>$label,'value'=>$singleData['UserMaster']['id'], 'class' => 'input-large','checked'=>$checked)); ?>
                                        <br/>
                                    <?php } ?>
                                </div>
                                <div class="form-group btns margin-bot-30">
                                    <div class="col-sm-8 col-lg-9 col-sm-offset-4 col-lg-offset-3">
                                        <button type="submit" class="btn btn-primary ripple-effect">Save users</button>
                                    </div>
                                </div>
                                <?php echo $this->Form->end(); ?>
                                
                                <?php }else{ ?>
                                    You are not allowed to make changes in subscriptions. Contact your administrator.
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




