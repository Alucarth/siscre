<?php $this->load->view('leads/front/partial/header'); ?>

<link rel="stylesheet" media="screen" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
<style>
    .form_float__input.error {
        border: 1px solid red;
    }
</style>
<?php echo form_open(); ?>
<?php echo form_close(); ?>

<div class="main" role="mainpage">
    <div class="hero_widget" role="mainline">
        <div class="wrapper" id="hero" role="heroForm">
            <form class="simple_form form_float" role="indexForm" id="frmApply" novalidate="novalidate" data-type="json" action="<?= site_url('leads/application') ?>" accept-charset="UTF-8" data-remote="true" method="post">

                <div class="hero_widget__body">
                    <?php $this->load->view('loans/widgets/select_loan_product'); ?>
                    <div class="hero_widget__form">
                        <div class="hero_widget__fields">
                            <div role="formFloatGroup" class="form_float__group tel optional application_email">
                                <input role="phoneMobile" autocomplete="false" class="string tel optional form_float__input" placeholder="Email" type="text" value="" name="application_email" id="application_email" />
                            </div>
                            <p class="form_float__help">Please provide your valid email address as it will be used for security code sending</p>                                        
                        </div>
                        <div class="hero_widget__fields">
                            <input type="submit" name="commit" value="Apply Loan" class="btn btn-primary btn btn-lg" role="takeMoneyMain" data-disable-with="Please wait..." />
                        </div>                                    
                    </div>
                </div>
                <br />
                <div class="hero_widget__calc_desc_up">
                    <div class="hero_widget__item">
                        We are a marketplace where you could easily apply for real estate loans. Promote your project and connect with business investors to realize a project.     
                    </div>
                </div>
            </form>


            <script>
                $(document).ready(function () {
                    $("#frmApply").submit(function (e) {
                        e.preventDefault();

                        if ($("#application_full_name").val() == '')
                        {
                            $("#application_full_name").addClass("error");
                            $("#application_full_name").parent().append('<div style="font-size:9px; color:red" id="div-error-fn">Please enter valid name</div>');
                            return;
                        }

                        if ($("#application_email").val() == '')
                        {
                            $("#application_email").addClass("error");
                            $("#application_email").parent().append('<div style="font-size:9px; color:red" id="div-error-em">Please enter valid email</div>');
                            return;
                        }

//                        if ($("#application_apply_amount").val() == '')
//                        {
//                            $("#application_apply_amount").addClass("error");
//                            $("#application_apply_amount").parent().append('<div style="font-size:9px; color:red" id="div-error-em">Please enter an amount</div>');
//                            return;
//                        }
//
//                        if ($("#application_loan_product").val() == '')
//                        {
//                            $("#application_loan_product").addClass("error");
//                            $("#application_loan_product").parent().append('<div style="font-size:9px; color:red" id="div-error-em">Please select a loan product</div>');
//                            return;
//                        }

                        $("input[role='takeMoneyMain']").prop("disabled", true);
                        $("input[role='takeMoneyMain']").val($("input[role='takeMoneyMain']").attr("data-disable-with"));

                        var url = $(this).attr("action");
                        var params = $(this).serialize();
                        params += '&softtoken=' + $("input[name='softtoken']").val();

                        $.post(url, params, function (data) {
                            if (data.status == "OK")
                            {
                                window.location.href = data.application_steps;
                            } else
                            {
                                alert(data.message);
                            }

                            $("input[role='takeMoneyMain']").prop("disabled", false);
                            $("input[role='takeMoneyMain']").val("Apply Loan");
                        }, 'json');
                    });

                    $("#application_full_name").click(function () {
                        $("#application_full_name").removeClass("error");
                        $("#div-error-fn").remove();
                    });

                    $("#application_email").click(function () {
                        $("#application_email").removeClass("error");
                        $("#div-error-em").remove();
                    });
                });
            </script>

        </div>
    </div>
    <div class="steps_represent">
        <div class="wrapper">
            <div class="steps_represent__title h1">5 Easy Steps to apply for a Real Estate loan</div>
            <div class="steps_represent__list">
                <div class="steps_represent__item">
                    <div class="steps_represent__img">

                        <span class="fa fa-address-book-o" style="position: absolute;
                              top: -5px;
                              font-size: 70px;
                              left: 0px;
                              color: #2ebde0"></span>
                    </div>
                    <div class="steps_represent__desc">
                        <div class="steps_represent__name normal bold black">Register</div>
                        <p class="steps_represent__text">Complete the application process in 5 minutes</p>
                    </div>
                </div>
                <div class="steps_represent__item">
                    <div class="steps_represent__img">

                        <span class="fa fa-file-text" style="position: absolute;
                              top: -5px;
                              font-size: 70px;
                              left: 0px;
                              color: #2ebde0"></span>
                    </div>
                    <div class="steps_represent__desc">
                        <div class="steps_represent__name normal bold black">
                            Fill in all <br/>required <br/>questions
                        </div>
                        <p class="steps_represent__text"></p>
                    </div>
                </div>
                <div class="steps_represent__item">
                    <div class="steps_represent__img">

                        <span class="fa fa-file-text-o" style="position: absolute;
                              top: -5px;
                              font-size: 70px;
                              left: 0px;
                              color: #2ebde0"></span>
                    </div>
                    <div class="steps_represent__desc">
                        <div class="steps_represent__name normal bold black">Upload <br/>required <br/>documents</div>
                        <p class="steps_represent__text"></p>
                    </div>
                </div>
                <div class="steps_represent__item">
                    <div class="steps_represent__img">

                        <span class="fa fa-tags" style="position: absolute;
                              top: -5px;
                              font-size: 70px;
                              left: 0px;
                              color: #2ebde0"></span>
                    </div>
                    <div class="steps_represent__desc">
                        <div class="steps_represent__name normal bold black">Promote the <br/>project</div>
                        <p class="steps_represent__text"></p>
                    </div>
                </div>
                <div class="steps_represent__item">
                    <div class="steps_represent__img">

                        <span class="fa fa-money" style="position: absolute;
                              top: -5px;
                              font-size: 70px;
                              left: 0px;
                              color: #2ebde0"></span>
                    </div>
                    <div class="steps_represent__desc">
                        <div class="steps_represent__name normal bold black">Get <br/>Funded</div>
                        <p class="steps_represent__text"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="target_group">
        <div class="wrapper">
            <div class="target_group__wrap">
                <div class="target_group__title h1 light">Who can use our
                    <br> service?</div>
                <div class="target_group__list">
                    <div class="target_group__items">
                        <div class="target_group__item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="53" height="53" viewbox="0 0 53 53" class="target_group__img target_group__img-age">
                            <g fill="none" fill-rule="evenodd">
                            <path d="M51.753 26.627c0 13.877-11.25 25.127-25.127 25.127C12.75 51.754 1.5 40.504 1.5 26.627 1.5 12.75 12.75 1.5 26.626 1.5c13.877 0 25.127 11.249 25.127 25.126"></path>
                            <path stroke="#FFF" stroke-width="2" d="M51.753 26.627c0 13.877-11.25 25.127-25.127 25.127C12.75 51.754 1.5 40.504 1.5 26.627 1.5 12.75 12.75 1.5 26.626 1.5c13.877 0 25.127 11.249 25.127 25.126z"></path>
                            <path fill="#FFF" d="M15.236 26.627a2.504 2.504 0 1 1 0-5.008 2.504 2.504 0 0 1 0 5.008M38.23 26.627a2.504 2.504 0 1 1 0-5.008 2.504 2.504 0 0 1 0 5.008"></path>
                            <path d="M39.49 33.41c-1.26 5-7.504 8.5-12.863 8.5-6.17 0-11.486-3.656-13.9-8.92-.454-.991 27.03-.64 26.763.42"></path>
                            <path stroke="#FFF" stroke-width="2" d="M39.49 33.41c-1.26 5-7.504 8.5-12.863 8.5-6.17 0-11.486-3.656-13.9-8.92-.454-.991 27.03-.64 26.763.42z"></path>
                            </g>
                            </svg>
                            <div class="target_group__desc">
                                <p class="target_group__name light bold">Loan originators:</p>
                                <p class="target_group__text light">Business Entities</p>
                            </div>
                        </div>
                        <div class="target_group__item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="43" height="53" viewbox="0 0 43 53" class="target_group__img target_group__img-location">
                            <g fill="none" fill-rule="evenodd">
                            <path d="M41.74 21.62c0 18.736-20.12 30.134-20.12 30.134S1.5 41.392 1.5 21.62C1.5 10.509 10.508 1.5 21.62 1.5c11.113 0 20.12 9.009 20.12 20.12"></path>
                            <path stroke="#FFF" stroke-width="2" d="M41.74 21.62c0 18.736-20.12 30.134-20.12 30.134S1.5 41.392 1.5 21.62C1.5 10.509 10.508 1.5 21.62 1.5c11.113 0 20.12 9.009 20.12 20.12z"></path>
                            <path d="M29.262 18.985a7.641 7.641 0 1 1-15.283 0 7.642 7.642 0 1 1 15.283 0"></path>
                            <path stroke="#FFF" stroke-width="2" d="M29.262 18.985a7.641 7.641 0 1 1-15.283 0 7.642 7.642 0 1 1 15.283 0z"></path>
                            </g>
                            </svg>
                            <div class="target_group__desc">
                                <p class="target_group__name light bold">Location</p>
                                <p class="target_group__text light">Netherlands</p>
                            </div>
                        </div>
                        <div class="target_group__item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="56" height="51" viewbox="0 0 56 51" class="target_group__img target_group__img-occupation">
                            <g fill="none" fill-rule="evenodd">
                            <path stroke="#FFF" stroke-width="2" d="M17.526 7.418c0-3.255 2.285-5.918 5.078-5.918h10.351c2.793 0 5.078 2.663 5.078 5.918"></path>
                            <path d="M42.059 50.008h-28.56c-6.6 0-12-5.4-12-12V19.915c0-6.6 5.4-12 12-12h28.56c6.6 0 12 5.4 12 12v18.093c0 6.6-5.4 12-12 12"></path>
                            <path stroke="#FFF" stroke-width="2" d="M42.059 50.008h-28.56c-6.6 0-12-5.4-12-12V19.915c0-6.6 5.4-12 12-12h28.56c6.6 0 12 5.4 12 12v18.093c0 6.6-5.4 12-12 12z"></path>
                            <path d="M6.22 27.46h15.66c0 2.875 1.999 5.204 4.465 5.204h1.797c2.466 0 4.465-2.33 4.465-5.203h16.732"></path>
                            <path stroke="#FFF" stroke-width="2" d="M6.22 27.46h15.66c0 2.875 1.999 5.204 4.465 5.204h1.797c2.466 0 4.465-2.33 4.465-5.203h16.732"></path>
                            </g>
                            </svg>
                            <div class="target_group__desc">
                                <p class="target_group__name light bold">Funding</p>
                                <p class="target_group__text light">from 500.000 €</p>
                            </div>
                        </div>
                    </div>
                    <button name="button" type="button" class="btn scrolltoelem">Get money now</button>
                    <script>
                        $(document).ready(function () {
                            $(".scrolltoelem").click(function () {
                                $("html, body").animate({scrollTop: 0}, "slow");
                            });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
    <div class="why_us">
        <div class="wrapper wrapper-large">
            <div class="why_us__title large center"></div>
            <div class="why_us__list">
                <div class="why_us__item why_us__item-5">
                    <div class="why_us__img">
                        <svg xmlns="http://www.w3.org/2000/svg" width="67" height="64" viewbox="0 0 67 64" class="why_us__img-5">
                        <g fill="none" fill-rule="evenodd">
                        <path fill="#FEFEFE" d="M49.712 27.715L26.082 49.78a4.5 4.5 0 0 1-6.34-.217L2.894 31.519a4.497 4.497 0 0 1 .216-6.338L26.742 3.115a4.497 4.497 0 0 1 6.338.217l16.85 18.045a4.497 4.497 0 0 1-.218 6.338"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M49.712 27.715L26.082 49.78a4.5 4.5 0 0 1-6.34-.217L2.894 31.519a4.497 4.497 0 0 1 .216-6.338L26.742 3.115a4.497 4.497 0 0 1 6.338.217l16.85 18.045a4.497 4.497 0 0 1-.218 6.338z"></path>
                        <path fill="#FEFEFE" d="M60.954 27.301l-23.63 22.066a4.5 4.5 0 0 1-6.34-.217L14.136 31.105a4.497 4.497 0 0 1 .216-6.338L37.984 2.701a4.497 4.497 0 0 1 6.338.217l16.85 18.045a4.497 4.497 0 0 1-.218 6.338"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M60.954 27.301l-23.63 22.066a4.5 4.5 0 0 1-6.34-.217L14.136 31.105a4.497 4.497 0 0 1 .216-6.338L37.984 2.701a4.497 4.497 0 0 1 6.338.217l16.85 18.045a4.497 4.497 0 0 1-.218 6.338z"></path>
                        <path fill="#FEFEFE" d="M59.364 62.963H7.285c-3.181 0-5.785-2.604-5.785-5.786V20.335c0-3.182 2.604-5.786 5.785-5.786h52.079c3.182 0 5.785 2.604 5.785 5.786v36.842c0 3.182-2.603 5.786-5.785 5.786"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M59.364 62.963H7.285c-3.181 0-5.785-2.604-5.785-5.786V20.335c0-3.182 2.604-5.786 5.785-5.786h52.079c3.182 0 5.785 2.604 5.785 5.786v36.842c0 3.182-2.603 5.786-5.785 5.786z"></path>
                        <path fill="#FEFEFE" d="M65.15 44.944H41.751a6.188 6.188 0 0 1 0-12.375H65.15v12.375z"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M65.15 44.944H41.751a6.188 6.188 0 0 1 0-12.375H65.15v12.375z"></path>
                        <path fill="#2ebde0" d="M42.712 40.876a2.12 2.12 0 1 1 .001-4.24 2.12 2.12 0 0 1 0 4.24"></path>
                        </g>
                        </svg>
                    </div>
                    <div class="why_us__desc">
                        <div class="why_us__subtitle bold">No offices visit</div>
                        <p class="why_us__text">&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp</p>
                    </div>
                </div>
                <div class="why_us__item why_us__item-2">
                    <div class="why_us__img">
                        <svg xmlns="http://www.w3.org/2000/svg" width="58" height="58" viewbox="0 0 58 58" class="why_us__img-6">
                        <g fill="none" fill-rule="evenodd">
                        <path fill="#FEFEFE" d="M56.636 29.139c0 15.186-12.312 27.497-27.498 27.497-15.186 0-27.496-12.311-27.496-27.497 0-15.187 12.31-27.497 27.496-27.497 15.186 0 27.498 12.31 27.498 27.497"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M56.636 29.139c0 15.186-12.312 27.497-27.498 27.497-15.186 0-27.496-12.311-27.496-27.497 0-15.187 12.31-27.497 27.496-27.497 15.186 0 27.498 12.31 27.498 27.497z"></path>
                        <path fill="#2ebde0" d="M16.673 29.139a2.74 2.74 0 1 1 0-5.48 2.74 2.74 0 0 1 0 5.48M41.836 29.139a2.74 2.74 0 1 1 0-5.48 2.74 2.74 0 0 1 0 5.48"></path>
                        <path fill="#FEFEFE" d="M43.215 36.562c-1.379 5.471-8.212 9.302-14.076 9.302-6.752 0-12.57-4.001-15.212-9.76-.496-1.086 29.58-.702 29.288.458"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M43.215 36.562c-1.379 5.471-8.212 9.302-14.076 9.302-6.752 0-12.57-4.001-15.212-9.76-.496-1.086 29.58-.702 29.288.458z"></path>
                        </g>
                        </svg>
                    </div>
                    <div class="why_us__desc">
                        <div class="why_us__subtitle bold">Business Investors</div>
                        <p class="why_us__text">Connect with different business investors</p>
                    </div>
                </div>
                <div class="why_us__item why_us__item-2">
                    <div class="why_us__img">
                        <svg xmlns="http://www.w3.org/2000/svg" width="53" height="67" viewbox="0 0 53 67" class="why_us__img-1">
                        <g fill="none" fill-rule="evenodd">
                        <path fill="#FEFEFE" d="M44.68 65.777H8.27c-3.998 0-7.27-3.271-7.27-7.27V8.27C1 4.272 4.271 1 8.27 1h36.41c3.998 0 7.27 3.272 7.27 7.27v50.237c0 3.999-3.272 7.27-7.27 7.27"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M44.68 65.777H8.27c-3.998 0-7.27-3.271-7.27-7.27V8.27C1 4.272 4.271 1 8.27 1h36.41c3.998 0 7.27 3.272 7.27 7.27v50.237c0 3.999-3.272 7.27-7.27 7.27z"></path>
                        <path fill="#FEFEFE" d="M43.76 21.524H9.19a1.855 1.855 0 0 1-1.854-1.855v-8.932c0-1.024.83-1.855 1.855-1.855H43.76c1.025 0 1.855.831 1.855 1.855v8.932c0 1.024-.83 1.855-1.855 1.855"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M43.76 21.524H9.19a1.855 1.855 0 0 1-1.854-1.855v-8.932c0-1.024.83-1.855 1.855-1.855H43.76c1.025 0 1.855.831 1.855 1.855v8.932c0 1.024-.83 1.855-1.855 1.855z"></path>
                        <path fill="#FEFEFE" d="M13.475 34.274a3.076 3.076 0 0 0 3.076-3.076v-.311a3.076 3.076 0 0 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.077"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M13.475 34.274a3.076 3.076 0 0 0 3.076-3.076v-.311a3.076 3.076 0 0 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.077z"></path>
                        <path fill="#FEFEFE" d="M26.475 34.274a3.076 3.076 0 0 0 3.076-3.076v-.311a3.076 3.076 0 0 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.077"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M26.475 34.274a3.076 3.076 0 0 0 3.076-3.076v-.311a3.076 3.076 0 0 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.077z"></path>
                        <path fill="#FEFEFE" d="M13.475 45.415a3.076 3.076 0 0 0 3.076-3.076v-.31a3.076 3.076 0 1 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.076"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M13.475 45.415a3.076 3.076 0 0 0 3.076-3.076v-.31a3.076 3.076 0 1 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.076z"></path>
                        <path fill="#FEFEFE" d="M26.475 45.415a3.076 3.076 0 0 0 3.076-3.076v-.31a3.076 3.076 0 1 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.076"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M26.475 45.415a3.076 3.076 0 0 0 3.076-3.076v-.31a3.076 3.076 0 1 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.076z"></path>
                        <path fill="#FEFEFE" d="M13.475 56.998a3.076 3.076 0 0 0 3.076-3.076v-.311a3.076 3.076 0 0 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.077"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M13.475 56.998a3.076 3.076 0 0 0 3.076-3.076v-.311a3.076 3.076 0 0 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.077z"></path>
                        <path fill="#FEFEFE" d="M26.475 56.998a3.076 3.076 0 0 0 3.076-3.076v-.311a3.076 3.076 0 0 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.077"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M26.475 56.998a3.076 3.076 0 0 0 3.076-3.076v-.311a3.076 3.076 0 0 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.077z"></path>
                        <path fill="#FEFEFE" d="M39.475 45.415a3.076 3.076 0 0 0 3.076-3.076v-.31a3.076 3.076 0 1 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.076"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M39.475 45.415a3.076 3.076 0 0 0 3.076-3.076v-.31a3.076 3.076 0 1 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.076z"></path>
                        <path fill="#2ebde0" d="M39.475 56.998a3.076 3.076 0 0 0 3.076-3.076v-.311a3.076 3.076 0 0 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.077"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M39.475 56.998a3.076 3.076 0 0 0 3.076-3.076v-.311a3.076 3.076 0 0 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.077z"></path>
                        <path fill="#FEFEFE" d="M39.475 34.274a3.076 3.076 0 0 0 3.076-3.076v-.311a3.076 3.076 0 0 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.077"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M39.475 34.274a3.076 3.076 0 0 0 3.076-3.076v-.311a3.076 3.076 0 0 0-6.152 0v.31a3.076 3.076 0 0 0 3.076 3.077z"></path>
                        </g>
                        </svg>
                    </div>
                    <div class="why_us__desc">
                        <div class="why_us__subtitle bold">Portfolio</div>
                        <p class="why_us__text">Keep your loan portfolio up to date.</p>
                    </div>
                </div>
            </div>
            <div class="why_us__list">
                <div class="why_us__item why_us__item-2">
                    <div class="why_us__img">
                        <svg xmlns="http://www.w3.org/2000/svg" width="75" height="65" viewbox="0 0 75 65" class="why_us__img-2">
                        <g fill="none" fill-rule="evenodd">
                        <path fill="#FEFEFE" d="M6.817 63.515h46.125c2.936 0 5.316-2.306 5.316-5.153V5.113c0-2.007-1.678-3.632-3.748-3.632H15.882c-2.07 0-3.749 1.625-3.749 3.632v53.249c0 2.847-2.38 5.153-5.316 5.153-2.937 0-5.317-2.306-5.317-5.153v-3.559h10.633"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M6.817 63.515h46.125c2.936 0 5.316-2.306 5.316-5.153V5.113c0-2.007-1.678-3.632-3.748-3.632H15.882c-2.07 0-3.749 1.625-3.749 3.632v53.249c0 2.847-2.38 5.153-5.316 5.153-2.937 0-5.317-2.306-5.317-5.153v-3.559h10.633"></path>
                        <path fill="#FEFEFE" d="M11.732 12.642h46.527"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M11.732 12.642h46.527"></path>
                        <path fill="#2ebde0" d="M19 8a1 1 0 1 1 .001-2.001A1 1 0 0 1 19 8M24 8a1 1 0 1 1 0-2 1 1 0 0 1 0 2M29 8a1 1 0 1 1 0-2 1 1 0 0 1 0 2"></path>
                        <path fill="#FEFEFE" d="M19.487 21.466h27.524"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M19.487 21.466h27.524"></path>
                        <path fill="#FEFEFE" d="M19.487 28.543h15.26"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M19.487 28.543h15.26"></path>
                        <g>
                        <path fill="#FEFEFE" d="M31.503 55.23H51.03"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M31.503 55.23H51.03"></path>
                        </g>
                        <path fill="#FEFEFE" d="M51.293 42.47l-2.803-2.876a1.713 1.713 0 0 1 .048-2.446L67.609 19.04a3.811 3.811 0 0 1 5.337.102 3.692 3.692 0 0 1-.103 5.267L53.771 42.517a1.77 1.77 0 0 1-2.478-.047"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M51.293 42.47l-2.803-2.876a1.713 1.713 0 0 1 .048-2.446L67.609 19.04a3.811 3.811 0 0 1 5.337.102 3.692 3.692 0 0 1-.103 5.267L53.771 42.517a1.77 1.77 0 0 1-2.478-.047z"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M47.586 38l-2.527 5.935c-.262.615.393 1.227 1.098 1.026L53 43"></path>
                        </g>
                        </svg>
                    </div>
                    <div class="why_us__desc">
                        <div class="why_us__subtitle bold">Clear overview</div>
                        <p class="why_us__text">Our platform provides a proper insight <br/>of the loan conditions</p>
                    </div>
                </div>
                <div class="why_us__item why_us__item-4">
                    <div class="why_us__img">
                        <svg xmlns="http://www.w3.org/2000/svg" width="87" height="67" viewbox="0 0 87 67" class="why_us__img-4">
                        <g fill="none" fill-rule="evenodd">
                        <path fill="#FEFEFE" d="M17.994 49.216h57.88c2.279 0 4.126-1.986 4.126-4.437V5.437C80 2.987 78.153 1 75.874 1h-57.88c-2.279 0-4.127 1.987-4.127 4.437v39.342c0 2.451 1.848 4.437 4.127 4.437"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M17.994 49.216h57.88c2.279 0 4.126-1.986 4.126-4.437V5.437C80 2.987 78.153 1 75.874 1h-57.88c-2.279 0-4.127 1.987-4.127 4.437v39.342c0 2.451 1.848 4.437 4.127 4.437z"></path>
                        <path fill="#FEFEFE" d="M15.569 52.913h64.358c3.354 0 6.073-2.924 6.073-6.53 0-.764-.576-1.383-1.286-1.383H10.78c-.71 0-1.286.619-1.286 1.383 0 3.606 2.72 6.53 6.074 6.53"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M15.569 52.913h64.358c3.354 0 6.073-2.924 6.073-6.53 0-.764-.576-1.383-1.286-1.383H10.78c-.71 0-1.286.619-1.286 1.383 0 3.606 2.72 6.53 6.074 6.53z"></path>
                        <path fill="#FEFEFE" d="M6.546 65.103h15.696c2.797 0 5.085-2.664 5.085-5.92V25.23c0-3.256-2.288-5.92-5.085-5.92H6.546c-2.796 0-5.084 2.664-5.084 5.92v33.953c0 3.256 2.288 5.92 5.084 5.92"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M6.546 65.103h15.696c2.797 0 5.085-2.664 5.085-5.92V25.23c0-3.256-2.288-5.92-5.085-5.92H6.546c-2.796 0-5.084 2.664-5.084 5.92v33.953c0 3.256 2.288 5.92 5.084 5.92z"></path>
                        <path fill="#2ebde0" d="M14.394 62.024a1.675 1.675 0 1 0-.001-3.35 1.675 1.675 0 0 0 0 3.35"></path>
                        <path fill="#FEFEFE" d="M20.99 32.25H7.478"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M20.99 32.25H7.478"></path>
                        <path fill="#FEFEFE" d="M20.99 38.417H7.478"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M20.99 38.417H7.478"></path>
                        <g>
                        <path fill="#FEFEFE" d="M20.99 44.834H7.478"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M20.99 44.834H7.478"></path>
                        </g>
                        <path fill="#2ebde0" d="M47.088 6.823a.912.912 0 1 0 0-1.824.912.912 0 0 0 0 1.824"></path>
                        </g>
                        </svg>
                    </div>
                    <div class="why_us__desc">
                        <div class="why_us__subtitle bold">Real Estate projects</div>
                        <p class="why_us__text">We give you the opportunity to realize your real estate projects.</p>
                    </div>
                </div>
                <div class="why_us__item why_us__item-4">
                    <div class="why_us__item why_us__item-5">
                        <div class="why_us__img">
                            <svg xmlns="http://www.w3.org/2000/svg" width="67" height="64" viewbox="0 0 67 64" class="why_us__img-5">
                            <g fill="none" fill-rule="evenodd">
                            <path fill="#FEFEFE" d="M49.712 27.715L26.082 49.78a4.5 4.5 0 0 1-6.34-.217L2.894 31.519a4.497 4.497 0 0 1 .216-6.338L26.742 3.115a4.497 4.497 0 0 1 6.338.217l16.85 18.045a4.497 4.497 0 0 1-.218 6.338"></path>
                            <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M49.712 27.715L26.082 49.78a4.5 4.5 0 0 1-6.34-.217L2.894 31.519a4.497 4.497 0 0 1 .216-6.338L26.742 3.115a4.497 4.497 0 0 1 6.338.217l16.85 18.045a4.497 4.497 0 0 1-.218 6.338z"></path>
                            <path fill="#FEFEFE" d="M60.954 27.301l-23.63 22.066a4.5 4.5 0 0 1-6.34-.217L14.136 31.105a4.497 4.497 0 0 1 .216-6.338L37.984 2.701a4.497 4.497 0 0 1 6.338.217l16.85 18.045a4.497 4.497 0 0 1-.218 6.338"></path>
                            <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M60.954 27.301l-23.63 22.066a4.5 4.5 0 0 1-6.34-.217L14.136 31.105a4.497 4.497 0 0 1 .216-6.338L37.984 2.701a4.497 4.497 0 0 1 6.338.217l16.85 18.045a4.497 4.497 0 0 1-.218 6.338z"></path>
                            <path fill="#FEFEFE" d="M59.364 62.963H7.285c-3.181 0-5.785-2.604-5.785-5.786V20.335c0-3.182 2.604-5.786 5.785-5.786h52.079c3.182 0 5.785 2.604 5.785 5.786v36.842c0 3.182-2.603 5.786-5.785 5.786"></path>
                            <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M59.364 62.963H7.285c-3.181 0-5.785-2.604-5.785-5.786V20.335c0-3.182 2.604-5.786 5.785-5.786h52.079c3.182 0 5.785 2.604 5.785 5.786v36.842c0 3.182-2.603 5.786-5.785 5.786z"></path>
                            <path fill="#FEFEFE" d="M65.15 44.944H41.751a6.188 6.188 0 0 1 0-12.375H65.15v12.375z"></path>
                            <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M65.15 44.944H41.751a6.188 6.188 0 0 1 0-12.375H65.15v12.375z"></path>
                            <path fill="#2ebde0" d="M42.712 40.876a2.12 2.12 0 1 1 .001-4.24 2.12 2.12 0 0 1 0 4.24"></path>
                            </g>
                            </svg>
                        </div>
                        <div class="why_us__desc">
                            <div class="why_us__subtitle bold">Transparent Costs</div>
                            <p class="why_us__text">The lender pays entree fee, success fee and administration fee for using the platform.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="why_us__list">
                <div class="why_us__item why_us__item-5">
                    <div class="why_us__img">
                        <svg xmlns="http://www.w3.org/2000/svg" width="58" height="58" viewbox="0 0 58 58" class="why_us__img-6">
                        <g fill="none" fill-rule="evenodd">
                        <path fill="#FEFEFE" d="M56.636 29.139c0 15.186-12.312 27.497-27.498 27.497-15.186 0-27.496-12.311-27.496-27.497 0-15.187 12.31-27.497 27.496-27.497 15.186 0 27.498 12.31 27.498 27.497"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M56.636 29.139c0 15.186-12.312 27.497-27.498 27.497-15.186 0-27.496-12.311-27.496-27.497 0-15.187 12.31-27.497 27.496-27.497 15.186 0 27.498 12.31 27.498 27.497z"></path>
                        <path fill="#2ebde0" d="M16.673 29.139a2.74 2.74 0 1 1 0-5.48 2.74 2.74 0 0 1 0 5.48M41.836 29.139a2.74 2.74 0 1 1 0-5.48 2.74 2.74 0 0 1 0 5.48"></path>
                        <path fill="#FEFEFE" d="M43.215 36.562c-1.379 5.471-8.212 9.302-14.076 9.302-6.752 0-12.57-4.001-15.212-9.76-.496-1.086 29.58-.702 29.288.458"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M43.215 36.562c-1.379 5.471-8.212 9.302-14.076 9.302-6.752 0-12.57-4.001-15.212-9.76-.496-1.086 29.58-.702 29.288.458z"></path>
                        </g>
                        </svg>
                    </div>
                    <div class="why_us__desc">
                        <div class="why_us__subtitle bold">Helping you make life easy</div>
                        <p class="why_us__text">We are hoping to help you find the best loan conditions tailored to your needs</p>
                    </div>
                </div>
                <div class="why_us__item why_us__item-6">
<!--                    <div class="why_us__img">
                        <svg xmlns="http://www.w3.org/2000/svg" width="58" height="58" viewbox="0 0 58 58" class="why_us__img-6">
                        <g fill="none" fill-rule="evenodd">
                        <path fill="#FEFEFE" d="M56.636 29.139c0 15.186-12.312 27.497-27.498 27.497-15.186 0-27.496-12.311-27.496-27.497 0-15.187 12.31-27.497 27.496-27.497 15.186 0 27.498 12.31 27.498 27.497"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M56.636 29.139c0 15.186-12.312 27.497-27.498 27.497-15.186 0-27.496-12.311-27.496-27.497 0-15.187 12.31-27.497 27.496-27.497 15.186 0 27.498 12.31 27.498 27.497z"></path>
                        <path fill="#2ebde0" d="M16.673 29.139a2.74 2.74 0 1 1 0-5.48 2.74 2.74 0 0 1 0 5.48M41.836 29.139a2.74 2.74 0 1 1 0-5.48 2.74 2.74 0 0 1 0 5.48"></path>
                        <path fill="#FEFEFE" d="M43.215 36.562c-1.379 5.471-8.212 9.302-14.076 9.302-6.752 0-12.57-4.001-15.212-9.76-.496-1.086 29.58-.702 29.288.458"></path>
                        <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M43.215 36.562c-1.379 5.471-8.212 9.302-14.076 9.302-6.752 0-12.57-4.001-15.212-9.76-.496-1.086 29.58-.702 29.288.458z"></path>
                        </g>
                        </svg>
                    </div>
                    <div class="why_us__desc">
                        <div class="why_us__subtitle bold">Helping you make life easy</div>
                        <p class="why_us__text">We are hoping to help you find the best loan conditions tailored to your needs</p>
                    </div>-->
                </div>
                <span style="visibility: hidden;"><div class="why_us__item why_us__item-6"><div class="why_us__img"><svg xmlns="http://www.w3.org/2000/svg" width="58" height="58" viewbox="0 0 58 58" class="why_us__img-6">
                            <g fill="none" fill-rule="evenodd">
                            <path fill="#FEFEFE" d="M56.636 29.139c0 15.186-12.312 27.497-27.498 27.497-15.186 0-27.496-12.311-27.496-27.497 0-15.187 12.31-27.497 27.496-27.497 15.186 0 27.498 12.31 27.498 27.497"></path>
                            <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M56.636 29.139c0 15.186-12.312 27.497-27.498 27.497-15.186 0-27.496-12.311-27.496-27.497 0-15.187 12.31-27.497 27.496-27.497 15.186 0 27.498 12.31 27.498 27.497z"></path>
                            <path fill="#2ebde0" d="M16.673 29.139a2.74 2.74 0 1 1 0-5.48 2.74 2.74 0 0 1 0 5.48M41.836 29.139a2.74 2.74 0 1 1 0-5.48 2.74 2.74 0 0 1 0 5.48"></path>
                            <path fill="#FEFEFE" d="M43.215 36.562c-1.379 5.471-8.212 9.302-14.076 9.302-6.752 0-12.57-4.001-15.212-9.76-.496-1.086 29.58-.702 29.288.458"></path>
                            <path stroke="#2ebde0" stroke-linecap="round" stroke-width="2" d="M43.215 36.562c-1.379 5.471-8.212 9.302-14.076 9.302-6.752 0-12.57-4.001-15.212-9.76-.496-1.086 29.58-.702 29.288.458z"></path>
                            </g>
                            </svg>
                        </div><div class="why_us__desc"><div class="why_us__subtitle bold empty">Helping you make life easy</div><p class="why_us__text">We are willing to help you find the best financial solution tailored to your needs.</p></div></div></span></div>
            <div class="why_us__action">
                <button name="button" type="button" class="btn block-center scrolltoelem">Apply now</button>
            </div>
            <div class="h4 why_us__disclamer center"><span style="color: grey"><p>Maximum annual rate 438%</p></span></div>
        </div>
    </div>
    <div class="review">
        <div class="wrapper">
            <div class="review__list slick" role="review">
                <div class="review__post">
                    <div class="review__body normal center black">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sollicitudin auctor sapien at bibendum. Morbi in tempus enim. Pellentesque dapibus elit sed purus feugiat tristique vel eu ipsum. Morbi vitae turpis eu eros vestibulum tempor. Phasellus pulvinar, libero at pellentesque sagittis, sem ligula sodales quam, a tristique elit mauris in mi. Phasellus aliquam rhoncus feugiat. Proin pulvinar facilisis arcu quis euismod. Morbi faucibus magna ac urna mollis, sit amet congue massa lacinia. Suspendisse dignissim sollicitudin est, nec pellentesque tortor sodales sit amet. 
                    </div>
                    <div class="review__author">
                        <div class="review__author__name normal bold black center">John Jones</div>
                        <div class="review__author__age normal lightblue center">Facebook page review</div>
                    </div>
                </div>
                <div class="review__post">
                    <div class="review__body normal center black">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sollicitudin auctor sapien at bibendum. Morbi in tempus enim. Pellentesque dapibus elit sed purus feugiat tristique vel eu ipsum. Morbi vitae turpis eu eros vestibulum tempor. Phasellus pulvinar, libero at pellentesque sagittis, sem ligula sodales quam, a tristique elit mauris in mi. Phasellus aliquam rhoncus feugiat. Proin pulvinar facilisis arcu quis euismod. Morbi faucibus magna ac urna mollis, sit amet congue massa lacinia. Suspendisse dignissim sollicitudin est, nec pellentesque tortor sodales sit amet. 
                    </div>
                    <div class="review__author">
                        <div class="review__author__name normal bold black center">Myke Tyson</div>
                        <div class="review__author__age normal lightblue center">Facebook page review</div>
                    </div>
                </div>
                <div class="review__post">
                    <div class="review__body normal center black">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec sollicitudin auctor sapien at bibendum. Morbi in tempus enim. Pellentesque dapibus elit sed purus feugiat tristique vel eu ipsum. Morbi vitae turpis eu eros vestibulum tempor. Phasellus pulvinar, libero at pellentesque sagittis, sem ligula sodales quam, a tristique elit mauris in mi. Phasellus aliquam rhoncus feugiat. Proin pulvinar facilisis arcu quis euismod. Morbi faucibus magna ac urna mollis, sit amet congue massa lacinia. Suspendisse dignissim sollicitudin est, nec pellentesque tortor sodales sit amet. 
                    </div>
                    <div class="review__author">
                        <div class="review__author__name normal bold black center">Coner McGregor</div>
                        <div class="review__author__age normal lightblue center">Facebook page review</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="blog_posts">
        <div class="wrapper">
            <div class="h1 center">Our blog</div>
            <div class="blog__items blog__items-home">
                <a class="blog_item" href="javascript:void(0)">
                    <div class="blog_item__img"></div>
                    <div class="blog_item__info">
                        <div class="blog_item__date normal lightblue">Jan 25, 2020</div>
                        <div class="blog_item__title normal bold black">Morbi euismod sagittis hendrerit. Proin ac sapien sollicitudin purus eleifend eleifend. </div>
                    </div>
                </a>
                <a class="blog_item" href="javascript:void(0)">
                    <div class="blog_item__img"></div>
                    <div class="blog_item__info">
                        <div class="blog_item__date normal lightblue">Feb 01, 2020</div>
                        <div class="blog_item__title normal bold black">Morbi euismod sagittis hendrerit. Proin ac sapien sollicitudin purus eleifend eleifend. </div>
                    </div>
                </a>
            </div><a class="btn block-center" href="en/blog.html">Read in our blog</a></div>
    </div>
    <div class="partners">
        <div class="wrapper">
            <div class="partners__title h1 center">Our Partners</div>
            <div class="partners__list slick" role="partners">
                <div class="partner__logo"><img class="partner__img" width="156" src="<?= site_url('leads/') ?>assets/front/partners/eleven_logo.png" alt="Eleven@2x" /></div>
            </div>
        </div>
    </div>
    <div class="hero_loans">
        <div class="wrapper">
            <div class="hero_loans__wrap">
                <div class="hero_loans__title h1 center light">Cash Loans for any Purpose.
                    <br />100% Online Transaction</div>
                <button name="button" type="button" class="btn block-center scrolltoelem" data-scrollto="@mainline">Apply now</button>
            </div>
        </div>
    </div>
</div>



<?php $this->load->view('leads/front/partial/footer'); ?>            


