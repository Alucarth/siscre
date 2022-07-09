<!doctype html>

<html class="no-js" lang="en">

    <meta http-equiv="content-type" content="text/html;charset=utf-8" />

    <head>

        <meta charset="utf-8">

        <meta http-equiv="x-ua-compatible" content="ie=edge">

        <title> <?=APP_NAME?> <?=SYSTEM_VERSION;?> - Sistema de administracion de préstamos </title>

        <meta name="description" content="">

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="apple-touch-icon" href="apple-touch-icon.html">

        <!-- Place favicon.ico in the root directory -->

        <link rel="stylesheet" href="<?= base_url('modular-admin/css/vendor.css?v=' . APP_VERSION) ?>">

        <link rel="stylesheet" href="<?= base_url('modular-admin/css/app.css') ?>">

        <!-- Theme initialization -->



        <script>

            BASE_URL = '<?php echo base_url(); ?>';

            SITE_URL = '<?php echo site_url('/siscre'); ?>';

            TOKEN_HASH = '<?php echo $this->security->get_csrf_hash(); ?>';

        </script>



        <!-- Toastr style -->

        <link href="<?php echo base_url(); ?>css/plugins/toastr/toastr.min.css" rel="stylesheet">

        <link href="<?php echo base_url(); ?>fonts/font-awesome/css/font-awesome.css" rel="stylesheet">



        <link href="<?php echo base_url(); ?>css/animate.css" rel="stylesheet">



        <!-- Data Tables -->

        <link href="<?php echo base_url(); ?>css/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet">

        <link href="<?php echo base_url(); ?>css/plugins/dataTables/dataTables.responsive.css" rel="stylesheet">

        <link href="<?php echo base_url(); ?>css/plugins/dataTables/dataTables.tableTools.min.css" rel="stylesheet">

        <link href="<?php echo base_url(); ?>js/alertifyjs/css/alertify.css" rel="stylesheet">

        <!-- Mainly scripts -->

        <script src="<?php echo base_url(); ?>js/jquery-2.1.1.js"></script>

        <script src="<?php echo base_url(); ?>js/bootstrap.min.js"></script>

        <script src="<?php echo base_url(); ?>js/plugins/metisMenu/jquery.metisMenu.js"></script>

        <script src="<?php echo base_url(); ?>js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

        

        <script src="<?php echo base_url('modular-admin/js/vendor.js?v=' . APP_VERSION) ?>"></script>



        <script src="<?php echo base_url(); ?>js/manage_tables.js"></script>

        <script src="<?php echo base_url(); ?>js/jquery.form.min.js"></script>



        <!-- Data Tables -->

        <script type="text/javascript" language="javascript" charset="UTF-8" src="<?php echo base_url(); ?>js/plugins/dataTables/jquery.dataTables.js?v=<?=APP_VERSION;?>"></script>

        <script type="text/javascript" language="javascript" charset="UTF-8" src="<?php echo base_url(); ?>js/plugins/dataTables/dataTables.bootstrap.js"></script>

        <script type="text/javascript" language="javascript" charset="UTF-8" src="<?php echo base_url(); ?>js/plugins/dataTables/dataTables.responsive.js"></script>

        <script type="text/javascript" language="javascript" charset="UTF-8" src="<?php echo base_url(); ?>js/plugins/dataTables/dataTables.tableTools.min.js"></script>

        <script type="text/javascript" language="javascript" charset="UTF-8" src="<?php echo base_url(); ?>js/plugins/dataTables/fnReloadAjax.js?v=1"></script>

        <!-- Data Tables -->



        <!-- datatable editor bootstrap setup - start -->

        <script type="text/javascript" charset="utf-8" src="<?php echo base_url(); ?>js/plugins/dataTables/editor/js/dataTables.editor.js"></script>

        <script type="text/javascript" charset="utf-8" src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>



        <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.bootstrap.min.js"></script>



        <!--<script type="text/javascript" language="javascript" src="https://editor.datatables.net/extensions/Editor/js/editor.bootstrap.min.js"></script>-->

        <script type="text/javascript" language="javascript" src="<?php echo base_url(); ?>js/plugins/dataTables/editor/js/editor.bootstrap.js"></script>

        <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>



        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.1/css/buttons.bootstrap.min.css">

        <link rel="stylesheet" type="text/css" href="https://editor.datatables.net/extensions/Editor/css/editor.bootstrap.min.css" />

        <!-- datatable editor bootstrap setup - end -->



        <link href="<?= base_url(); ?>css/plugins/datapicker/datepicker3.css" rel="stylesheet">     

        

        <style>

            .kl-plugin {

                display: inline-block;

                padding: 2px;

                border-radius: 6px;

                border: 1px solid #ccc;

                background-color: #f3e798;

            }



            body {

                top: 0 !important;

            }



            .dt-buttons.btn-group {

                float:left;

            }



            .dataTables_filter select {

                float:right;

            }



            .dataTables_filter input[type="search"] {

                margin-left:6px;

            }



            .dataTables_processing {

                position: absolute;

                left: 50%;

                -webkit-transform: translateX(-50%);

                transform: translateX(-50%)

            }



            .autocomplete-suggestions {

                background: white;

                border: 1px solid #ccc;

                padding: 10px;

            }



            .table.table-bordered.dataTable {

                width:100% !important;

            }



            .btn.btn-default {

                color: #212529;

                background-color: #fff;

                border-color: #d7dde4;

            }

            

            .autocomplete-suggestions {

                overflow: auto;

            }

            

            .sup-notify {

                display: inline-block;

                background: red;

                height: 18px;

                width: 18px;

                line-height: normal;

                border-radius: 55%;

                margin-left: -6px;

                padding-top: 2px;

                text-align: center;

            }

            

            .sup-notify .counter {

                color: white !important;

                font-size: x-small;

                margin: 0 auto;

                display: inline-block;

                left: initial !important;

            }

            

            .dropdown-menu.show {

                padding: 0 !important;

            }

        </style>

    </head>

    <body>



        <input type="hidden" id="site_url" name="site_url" value="<?= site_url() ?>" />

        <?php echo form_open();echo form_close();?>



        <div class="main-wrapper">

            <div class="app" id="app">

                <header class="header">

                    <div class="header-block header-block-collapse d-lg-none d-xl-none">

                        <button class="collapse-btn" id="sidebar-collapse-btn">

                            <i class="fa fa-bars"></i>

                        </button>

                    </div>

                    <div class="header-block header-block-search">

                        <form role="search">

                            <div class="input-container">

                                <i class="fa fa-search"></i>

                                <input type="search" placeholder="Buscar">

                                <div class="underline"></div>

                            </div>

                        </form>

                    </div>

                    <div class="header-block header-block-buttons">



                        <!-- Multi-language plugin -->

                        <?php
                        /*
                        if (is_plugin_active('klanguage'))

                        {

                            $this->load->view('klanguage/main');

                        }
                        */    
                        ?>

                        <!-- Multi-language plugin -->



                    </div>

                    <div class="header-block header-block-nav">

                        <ul class="nav-profile">                            

                            <li class="notifications new">

                                <a href="#" data-toggle="dropdown">

                                    <i class="fa fa-envelope-o"></i>

                                    <?php if (count($messages) > 0): ?>

                                        <sup class="sup-notify">

                                            <span class="counter"><?= count($messages) ?></span>

                                        </sup>

                                    <?php endif; ?>

                                </a>

                                <div class="dropdown-menu notifications-dropdown-menu">





                                    <?php if (count($messages)): ?>

                                        <ul class="notifications-container">



                                            <?php foreach ($messages as $message): ?>

                                                <li>

                                                    <a href="<?= site_url("messages/view/" . $message->message_id) ?>" class="notification-item">

                                                        <div class="img-col">

                                                            <div class="img" style="background-image: url('<?= $message->profile_pic; ?>')"></div>

                                                        </div>

                                                        <div class="body-col">

                                                            <strong><?= $message->sender_name; ?></strong> te envié un mensaje. <strong><?= $message->header; ?></strong><br />

                                                            <small class="text-muted"><?= $message->str_timestamp; ?></small>

                                                            <small class="pull-right"><?= $message->hours_ago; ?></small>

                                                        </div>

                                                    </a>

                                                </li>

                                            <?php endforeach; ?>                                        



                                        </ul>

                                        <footer>

                                            <ul>

                                                <li>

                                                    <a href="<?= site_url("messages/inbox"); ?>" style="padding-top: 8px;padding-bottom: 8px;"> Ver todo </a>

                                                </li>

                                            </ul>

                                        </footer>



                                    <?php else: ?>

                                        <footer style="padding-top: 16px;padding-bottom: 16px;">

                                            <ul>

                                                <li>

                                                    <a href="javascript:void(0)" class="notification-item">

                                                        <div class="body-col">

                                                            No se encontraron resultados.

                                                        </div>

                                                    </a>

                                                </li>

                                            </ul>

                                        </footer>

                                    <?php endif; ?>



                                </div>                                 

                            </li>

                            <li class="notifications new">

                                <a href="#" data-toggle="dropdown">

                                    <i class="fa fa-bell-o"></i>

                                    <?php if (count($alerts) > 0): ?>

                                        <sup class="sup-notify">

                                            <span class="counter"><?= count($alerts) ?></span>

                                        </sup>

                                    <?php endif; ?>

                                </a>

                                <div class="dropdown-menu notifications-dropdown-menu">





                                    <?php if (count($alerts)): ?>

                                        <ul class="notifications-container" style="max-height: calc(85vh - 200px); overflow: auto;">



                                            <?php foreach ($alerts as $alert): ?>

                                                <li id="alert-<?=$alert->loan_id?>">

                                                    <div class="notification-item">

                                                        <div class="img-col">

                                                            <div class="img" style="background-image: url('<?= $alert->profile_pic; ?>')"></div>

                                                        </div>

                                                        <div class="body-col">

                                                            <a href="<?= site_url("loans/view/" . $alert->loan_id) ?>"><strong><?= $alert->client_name; ?></strong> <?= $alert->header; ?></a> <a href="javascript:void(0)" title="Delete" class="remove-notify" data-id="<?=$alert->loan_id;?>"><span class="fa fa-trash"></span></a><br />

                                                            <small class="text-muted"><?= $alert->str_timestamp; ?></small>

                                                            <small class="pull-right"><?= $alert->hours_ago; ?></small>

                                                        </div>

                                                    </div>                                                    

                                                </li>

                                            <?php endforeach; ?>                                        



                                        </ul>

                                        <footer>

                                            <ul>

                                                <li>

                                                    <a href="<?= site_url("loans"); ?>" style="padding-top: 8px;padding-bottom: 8px;"> Ver todas </a>

                                                </li>

                                            </ul>

                                        </footer>

                                    

                                    <script>

                                        $(document).ready(function(){

                                            $(".remove-notify").click(function(){

                                                var id = $(this).attr("data-id");

                                                var url = '<?=site_url('loans/ajax')?>';

                                                var params = {

                                                    ajax_type:15,

                                                    id:id,

                                                    softtoken:$("input[name='softtoken']").val()

                                                };

                                                $.post(url, params, function(data){

                                                    if ( data.status == "OK" )

                                                    {

                                                        window.location.reload();

                                                    }

                                                }, "json");

                                            });

                                        });

                                    </script>



                                    <?php else: ?>

                                        <footer style="padding-top: 16px;padding-bottom: 16px;">

                                            <ul>

                                                <li>

                                                    <a href="javascript:void(0)" class="notification-item">

                                                        <div class="body-col">

                                                            No se encontraron resultados.

                                                        </div>

                                                    </a>

                                                </li>

                                            </ul>

                                        </footer>

                                    <?php endif; ?>



                                </div>

                            </li>

                            <li class="profile dropdown">

                                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">

                                    

                                    <?php if (trim($user_info->photo_url) !== "" && file_exists( FCPATH  .  "/uploads/profile-" . $user_info->person_id . "/" . $user_info->photo_url ) ):  ?>

                                        <img src="<?= base_url("uploads/profile-" . $user_info->person_id . "/" . $user_info->photo_url); ?>" alt="" class="img media-object img-circle" />

                                    <?php else: ?>

                                        <img src="http://via.placeholder.com/80x80" alt="" class="img media-object img-circle" />

                                    <?php endif; ?>

                                    

                                    <?php if ( is_plugin_active("branches") && $user_info->role_id != CUSTOMER_ROLE_ID): ?>

                                        <span class="name"> <?= ucwords($user_info->first_name . " " . $user_info->last_name) . " (" . get_branch_name() . ")"; ?> </span>

                                    <?php else: ?>

                                        <span class="name"> <?= ucwords($user_info->first_name . " " . $user_info->last_name); ?> </span>

                                    <?php endif; ?>

                                        

                                </a>

                                <div class="dropdown-menu profile-dropdown-menu" aria-labelledby="dropdownMenu1">

                                    <?php if ( $user_info->role_id == CUSTOMER_ROLE_ID ): ?>

                                        <a class="dropdown-item" href="<?= site_url("customers/view/" . $user_info->person_id); ?>">

                                            <i class="fa fa-user icon"></i> Perfil 

                                        </a>

                                    <?php else:?>

                                        <a class="dropdown-item" href="<?= site_url("employees/view/" . $user_info->person_id); ?>">

                                            <i class="fa fa-user icon"></i> Perfil

                                        </a>

                                        <a class="dropdown-item" href="<?= site_url("customers"); ?>">

                                            <i class="fa fa-group icon"></i> Contactos </a>

                                        <a class="dropdown-item" href="<?= site_url("messages/inbox") ?>">

                                            <i class="fa fa-inbox icon"></i> Buzón </a>

                                        <div class="dropdown-divider"></div>

                                    <?php endif;?>

                                    

                                    <a class="dropdown-item" href="<?= site_url("home/logout")?>">

                                        <i class="fa fa-power-off icon"></i> Cerrar sesión </a>

                                </div>

                            </li>

                        </ul>

                    </div>

                </header>





                <?php $this->load->view('partial/sidebar'); ?>



                <div class="sidebar-overlay" id="sidebar-overlay"></div>

                <div class="sidebar-mobile-menu-handle" id="sidebar-mobile-menu-handle"></div>

                <div class="mobile-menu-handle"></div>





                <article class="content">











