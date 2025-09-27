<aside class="sidebar">
    <div class="sidebar-container">
        <div class="sidebar-header">
            <div class="brand">
                <?php if ( $this->config->item("app_logo") != '' ): ?>
                    <div>
                        <img src="<?=base_url('uploads/app/' . $this->config->item("app_logo"))?>" style="max-width: 75%;" />
                    </div>
                <?php else:?>
                    <div class="logo">
                        <span class="l l1"></span>
                        <span class="l l2"></span>
                        <span class="l l3"></span>
                        <span class="l l4"></span>
                        <span class="l l5"></span>
                    </div> <a href="<?=site_url('home');?>" style="color:white;text-decoration: none;"><?php echo APP_NAME; ?></a>
                <?php endif; ?>
            </div>
        </div>
        
        <nav class="menu">

            <ul class="sidebar-menu metismenu" id="sidebar-menu">

                <?php foreach ($allowed_modules->result() as $module) : ?>
                
                    <?php if ( $user_info->role_id == CUSTOMER_ROLE_ID && stristr($module->module_id, "leads") ) continue; ?>
                
                    <?php if ( $user_info->role_id == CUSTOMER_ROLE_ID && strpos(strtolower($module->module_id), 'home') !== false ): ?>
                        <?php $active = ( stristr("leads", $this->router->fetch_class()) && stristr("dashboard", $this->router->fetch_method()) ? 'active' : '' ); ?>
                
                        <li class="<?= $active; ?>">
                            <a href="<?php echo site_url('leads/dashboard'); ?>"> 
                                <?= $module->icons ?>
                                <span class="nav-label">Dashboard</span>
                            </a>
                        </li>
                        
                        <?php $active = ( stristr("leads", $this->router->fetch_class()) && stristr("loan_history", $this->router->fetch_method()) ? 'active' : '' ); ?>
                        <li class="<?=$active;?>">
                            <a href="<?php echo site_url("leads/loan_history"); ?>"> 
                                <span class="fa fa-money"></span>
                                <span class="nav-label">Loan History</span>
                            </a>
                        </li>
                        
                        <?php continue; ?>
                    <?php endif; ?>
                
                    <?php if ( $user_info->role_id == CUSTOMER_ROLE_ID && strpos(strtolower($module->module_id), 'customer') !== false ): ?>
                
                        <?php $active = ( stristr($module->module_id, $this->router->fetch_class()) ? 'active' : '' ); ?>
                
                        <li class="<?= $active; ?>">
                            <a href="<?php echo site_url("$module->module_id/view/" . $user_info->person_id); ?>"> 
                                <?= $module->icons ?>
                                <span class="nav-label">Profile</span>
                            </a>
                        </li>
                        
                        <?php continue; ?>
                
                    <?php endif; ?>
                
                    <?php $sub_menus = json_decode($module->sub_menus, true); ?>

                    <?php if (count($sub_menus) > 0): ?>
                        <?php $parent_active = ( stristr($module->module_id, $this->router->fetch_class()) ? 'active open' : '' ); ?>
                        <li class="nav-parent <?= $parent_active ?>">       

                            <a href="<?php echo site_url("$module->module_id"); ?>" title="<?php echo $this->lang->line('module_' . $module->module_id . '_desc'); ?>">
                                <?= $module->icons ?>
                                <span class="nav-label"><?php echo $this->lang->line("module_" . $module->module_id) != '' ? $this->lang->line("module_" . $module->module_id) : $module->label; ?></span>
                                <i class="fa arrow"></i>
                            </a>

                            <ul class="sidebar-nav">
                                <?php foreach ($sub_menus as $sub_menu_key => $sub_menu_url): ?>
                                    <?php $child_active = ( stristr($sub_menu_url, $this->router->fetch_method()) && stristr($module->module_id, $this->router->fetch_class()) ? 'active' : '' ); ?>
                                    <li class="<?= $child_active; ?>">
                                        <a href="<?php echo site_url("$module->module_id"); ?>/<?= $sub_menu_url ?>">
                                            <?= str_ireplace("item", rtrim($this->lang->line("module_" . $module->module_id), "s"), $sub_menu_key); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?> 
                            </ul>
                        </li>

                    <?php else: ?>
                        <?php $active = ( stristr($module->module_id, $this->router->fetch_class()) ? 'active' : '' ); ?>
                        <li class="<?= $active; ?>">
                            <a href="<?php echo site_url("$module->module_id"); ?>"> 
                                <?= $module->icons ?>
                                <span class="nav-label"><?= $this->lang->line("module_" . $module->module_id) != '' ? $this->lang->line("module_" . $module->module_id) : $module->label; ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</aside>

<div class="nano left-sidebar">
    <div class="nano-content">
        <ul class="nav nav-pills nav-stacked nav-inq">



        </ul>
    </div>
</div>