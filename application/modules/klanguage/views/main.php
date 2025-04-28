<div class="plg-language">

    <style>
        .plg-language .select2 {
	    display: none;
	}
        
        .plg-language {
            display:inline-block;
            margin-top: 12px;
        }

        .goog-te-banner-frame.skiptranslate, 
        .form-google-translate, #google_translate_element {
            display: none !important;
        }

        .goog-te-banner-frame {
            height: 0 !important;
            top: auto !important;
            bottom: 0px !important;
        }            
        
        .dropdown-menu.show {
            padding: 7px 0 0 14px;
        }
        .flagstrap {
            width: auto !important; 
        }
        
        .plg-language .dropdown-toggle::after {            
            color: black;
        }
        
    </style>

    <!-- flagstrap -->
    <link rel="stylesheet" href="//softreliance.com/assets/flagstrap/css/flags.css"/>         
    <script src="//softreliance.com/assets/flagstrap/js/jquery.flagstrap-v3.js"></script>
    <!-- flagstrap -->

    <span>Language: </span>
    <div id="options"
         data-input-name="country2"
         data-selected-country="AU">
    </div>

    <div class="form-group input-group form-google-translate">
        <div id="google_translate_element"></div>
        <script type="text/javascript">
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE, autoDisplay: false}, 'google_translate_element');
            }
        </script>
        <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" type="text/javascript"></script>
    </div>
    
    <script>
            $(document).ready(function () {
                var lang = getCookie("lang");
                var lang_value = getCookie("lang_val");
                
                lang_value = '<?=$this->config->item( 'language_used' )?>';
                
                if (lang_value != '')
                {
                    $("#options").attr("data-selected-country", lang_value);
                }

                $('#options').flagStrap({
                    countries: {
                        "au": "English",
                        "za": "Afrikaans",
                        "al": "Albanian",
                        "sa": "Arabic",
                        "am": "Armenian",
                        "az": "Azerbaijani",
                        "by": "Belarusian",
                        "bg": "Bulgarian",
                        "cn": "Chinese",
                        "hr": "Croatian",
                        "cz": "Czech",
                        "dk": "Danish",
                        "be": "Dutch",
                        "ee": "Estonian",
                        "fi": "Finnish",
                        "fr": "French",
                        "ge": "Georgian",
                        "de": "German",
                        "gr": "Greek",
                        "il": "Hebrew",
                        "in": "Hindi",
                        "hu": "Hungarian",
                        "is": "Icelandic",
                        "id": "Indonesian",
                        "it": "Italian",
                        "jp": "Japanese",
                        "kz": "Kazakh",
                        "kr": "Korean",
                        "kz" : "Kyrgyz",
                        "lv": "Latvian",
                        "lt": "Lithuanian",
                        "mk": "Macedonian",
                        "my": "Malay",
                        "mn": "Mongolian",
                        "no": "Norwegian",
                        "pl": "Polish",
                        "pt": "Portuguese",
                        "ro": "Romanian",
                        "ru": "Russian",
                        "sr": "Serbian",
                        "sk": "Slovak",
                        "sl": "Slovenian",
                        "af": "Somali",
                        "es": "Spanish",
                        "ke": "Swahili",
                        "se": "Swedish",
                        "th": "Thai",
                        "tr": "Turkish",
                        "ua": "Ukrainian",
                        "pk": "Urdu",
                        "uz": "Uzbek",
                        "vn": "Vietnamese",
                    },
                    buttonType: "btn-transparent",
                    labelMargin: "4px",
                    scrollable: true,
                    scrollableHeight: "350px",
                    show_caption: false,
                    onSelect: function (value, element, lang) {
                        var $frame = $('.goog-te-menu-frame:first');
                        
                        if ( $frame.length )
                        {
                            $frame.contents().find('.goog-te-menu2-item span.text:contains(' + lang + ')').get(0).click();
                            document.cookie = "lang=" + lang + ";";
                            document.cookie = "lang_val=" + value + ";";
                            $("#options").attr("data-selected-country", lang_value);
                        }
                        else
                        {
                            return false;
                        }
                    }
                });
                
                if (lang_value != '')
                {
                    setTimeout(function(){
                        $("#options").attr("data-selected-country", lang_value);
                        $("#options select[name='country2']").val(lang_value);
                        $("#options select[name='country2']").trigger('change');
                    }, 700);
                }
            });

            function getCookie(cname) {
                var name = cname + "=";
                var decodedCookie = decodeURIComponent(document.cookie);
                var ca = decodedCookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            }
    </script>

</div>