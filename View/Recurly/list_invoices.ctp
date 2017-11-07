<?php echo $this->element('cache_functions'); ?>
<div class="padding-block">
    <div class="container">
        <div class="row">
            <div class="alert alert-success alert-dismissible fade in" role="alert" style="display: none;">
                <button type="button" class="close custom_close" aria-label="Close"><span aria-hidden="true">×</span></button>
                <span id="message"></span>
            </div>
            <?php echo !empty($message) ? $message : ''; ?>
            <div class="tab-content">
                <div id="my-collections" class="tab-pane active tab_common my_collection_tab" role="tabpanel">
                    <?php if (isset($usersInvoices) && !empty($usersInvoices)) { ?>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="block single-usecase usecases orange">
                                <div class="case-item orange mychallenges user-tabular">
                                    <div class="table-heading row">
                                        <div class="form-group icon-right col-lg-6 col-sm-6">
                                            <div class="floatinglabels paper-input">
                                                <!-- Search -->
                                            </div>
                                        </div>
                                    </div>                           	
                                    <div class="reload_data">
                                        <div class="table-fixed-head  shadow-z-1">
                                            <div class="table-responsive-vertical table-striped">
                                                <table cellspacing="0" cellpadding="0" class="table table-hover table-mc-light-blue" id="table">
                                                    <thead>
                                                        <tr>
                                                            <th>State</th>
                                                            <th>Invoice Number</th>
                                                            <th>Sub total</th>
                                                            <th>Total</th>
<!--                                                            <th>Start Date</th>
                                                            <th>End Date</th>-->
                                                            <th>Created</th>
                                                            <th>Closed At</th>
                                                            <th>Download</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        if (isset($usersInvoices) && !empty($usersInvoices)) {
                                                            foreach ($usersInvoices as $singleInvoice) { 
                                                                //echo "<pre>";print_r($singleInvoice);echo "</pre>";exit;
                                                                //$singleInvoiceObj = $singleInvoice->getValues();?>
                                                        <tr>
                                                            <td><?php echo $singleInvoice->state; ?></td>
                                                            <td><?php echo $singleInvoice->invoice_number; ?></td>
                                                            <td><?php echo "$".($singleInvoice->subtotal_in_cents)/100; ?></td>
                                                            <td><?php echo "$".($singleInvoice->total_in_cents)/100; ?></td>
<!--                                                            <td><?php //echo !empty($singleInvoice->start_date) ? $singleInvoice->start_date->format('Y-m-d H:i:s') : "-"; ?></td>
                                                            <td><?php //echo !empty($singleInvoice->end_date) ? $singleInvoice->end_date->format('Y-m-d H:i:s') : "-"; ?></td>-->
                                                            <td><?php echo !empty($singleInvoice->created_at) ? $singleInvoice->created_at->format('Y-m-d H:i:s') : "-"; ?></td>
                                                            <td><?php echo !empty($singleInvoice->closed_at) ? $singleInvoice->closed_at->format('Y-m-d H:i:s') : "-"; ?></td>
                                                            <td><a target="_blank" href="<?php echo SITE_URL.'recurly/invoicePdf/'.$hashidObj->encode($singleInvoice->invoice_number).".pdf"; ?>">Download</a></td>
                                                        </tr>
                                                        <?php    }
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 orange">
    <!--                                <h2 class="block-title text-center">My <span class="orange-color"><?php echo $GLOBALS['LABELS']['MODULES']['HomePage']['collections']; ?></span></h2>-->
                            <p class="blankmessage text-center page">There are no <?php echo $GLOBALS['LABELS']['MODULES']['HomePage']['collections']; ?> created yet. 

            <!--                                    Click below to create a new <?php echo $GLOBALS['LABELS']['MODULES']['HomePage']['collection']; ?>.<br><a href="<?php echo SITE_URL . "create-a-collection" ?>" class="btn btn-primary">Create <?php echo $GLOBALS['LABELS']['MODULES']['HomePage']['collection']; ?></a>-->
                            </p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>