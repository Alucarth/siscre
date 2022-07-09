<!DOCTYPE html>
<html class="html">
    <head>
        <meta http-equiv="content-type" content="text/html;charset=utf-8" />
        <title>Lending Marketplace Online - Powered by <?=APP_NAME;?></title>

        <meta content="yes" name="apple-mobile-web-app-capable" />
        <meta content="width=device-width,initial-scale=1.0,minimum-scale=1.0,user-scalable=1" name="viewport" />    
        <meta content="Need a fast loan? Apply now and get approved within 24 hours! No collaterals, no tedious processing. Just fast cash by Online Loans - Powered by K-Loans." name="description" />
        <meta content="" name="keywords" />
        <meta property="og:title" content="Online Loans - Powered by K-Loans" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://softreliance.com/marketplace/details/8" />    
        <meta property="og:image:type" content="image/png" />
        <meta property="og:image:width" content="400" />
        <meta property="og:image:height" content="400" />
        <meta property="og:description" content="Online Loans - Powered by K-Loans is an online financial solution providing a new way for everyone to borrow money. Loan24/7, without collateral requirements and waiting time." />
        <meta property="og:locale" content="en_EN" />
        <meta property="og:site_name" content="Online Loans - Powered by K-Loans" />
        
        <link rel="stylesheet" media="screen" href="//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" />
        
        <link rel="stylesheet" media="screen" href="<?=site_url('leads/')?>assets/front/main/global/application.css?v=1" />
        <link rel="stylesheet" media="screen" href="<?=site_url('leads/')?>assets/front/main/desktop/application.css" />        
        
        <script  src="https://code.jquery.com/jquery-3.4.1.min.js"  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="  crossorigin="anonymous"></script>
        <script src="<?php echo site_url('leads/assets/bootstrap/js/bootstrap.js') ?>"></script>
        
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        
        <style>
            .modal-backdrop.show {
                opacity: .75;
            }
        </style>
        
        
        <script>
            $(document).ready(function(){
                $('select').select2({
                    theme: "theme",
                    minimumResultsForSearch: Infinity
                });
            });
        </script>
        
    </head>

    <body class="body">
        <div class="container" role="container">
            
            <?php if(isset($no_header) && $no_header): ?>
                <div class="navbar navbar-steps transform">
                    <div class="wrapper">
                        <a class="navbar__logo navbar__logo-steps" href="<?=site_url('leads/index')?>">
                            <img src="<?= (trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png"); ?>" style="width:100%" />
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="navbar navbar-home">
                <div class="wrapper">
                    <div class="navbar__wrap">
                        <div class="navbar__mobile_action">
                            <button name="button" type="button" class="navbar__burger" role="navbarBurger"><span></span></button>
                            <a class="navbar__logo" href="<?=  site_url('investors')?>" style="width:120px;">
                                <img src="<?= (trim($this->config->item("logo")) !== "") ? base_url("uploads/logo/" . $this->config->item('logo')) : base_url("uploads/common/no_img.png"); ?>" style="width:100%;max-height: 58px;" />
                            </a>
                        </div>
                        <div class="navbar__dark_bg" role="darkBg"></div>
                        <div class="navbar__nav" role="navbarNav">
                            <ul class="navbar__items">
                                <li class="navbar__item navbar__item-home_mobile">
                                    <a class="navbar__logo navbar__logo-mobile" href="<?=site_url('leads')?>">
                                        Logo Here
                                    </a>
                                </li>
                                <?php if($articles): ?>
                                    <?php foreach($articles->result() as $article): ?>
                                        <li class="navbar__item false"><a class="navbar__link" href="<?=site_url('leads/article/' . $article->article_id)?>"><?=ucwords($article->title);?></a></li>
                                    <?php endforeach; ?>
                                <?php endif;?>
                            </ul>                            
                        </div>
                        
                        <?php if( $this->session->userdata("session_leads_id") > 0 ): ?>
                            <div class="navbar__repeat">
                                <div class="navbar__item navbar__item-repeat navbar__item-repeat-icon">                                    
                                    <a class="navbar__link navbar__link-repeat" href="javascript:void(0)"><?=$leads_first_name;?></a>
                                </div>
                                <div class="navbar_sign_out" style="width:228px;">
                                    <a rel="nofollow" href="javascript:void(0)" id="btn-sign-out">Sign Out</a>
                                    <script>
                                        $(document).ready(function(){
                                            $("#btn-sign-out").click(function(){
                                                var url = '<?=site_url('leads/ajax');?>';
                                                var params = {
                                                    softtoken:$("input[name='softtoken']").val(),
                                                    type: 8
                                                };
                                                $.post(url, params, function(data){
                                                    if ( data.status == "OK" )
                                                    {
                                                        window.location.reload();
                                                    }
                                                }, 'json');
                                            });
                                        });
                                    </script>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="navbar__repeat navbar__repeat-default">
                                <div class="navbar__item navbar__item-repeat">
                                    <a data-modal="true" role="lkMain" class="navbar__link navbar__link-repeat btn-login" data-remote="true" href="javascript:void(0)">
                                        Login to your account
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
            <?php endif; ?>
