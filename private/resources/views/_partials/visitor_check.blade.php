@if ((bool)get_option('ads_protector', 0) === true && !request()->session()->has('VisitorStatus'))
    <form id="visitorCheck" action="{{ route('visitor-check') }}" style="display: none;">@csrf</form>

    <script src="https://cdn.jsdelivr.net/npm/js-base64@2.5.1/base64.min.js"></script>
    <script>
        var visitorCheckForm = $('#visitorCheck');

        function visitorCheckProcess() {
            $.ajax({
                dataType: 'json',
                type: 'POST',
                url: visitorCheckForm.attr('action'),
                data: visitorCheckForm.serialize(),
                success: function (result, status, xhr) {
                    //console.log(result);
                    if (result.ads) {
                        $(".ad-element").each(function (index, element) {
                            $(this).find('.ad-inner').html(Base64.decode($(this).attr('data-code')));
                        });
                    }
                    $(window).trigger('resize');
                },
                error: function (xhr, status, error) {
                    console.log('An error occurred: ' + xhr.status + ' ' + xhr.statusText);
                },
            });
        }

        if (app_vars.recaptcha_v3_site_key) {
            var recaptcha_script = document.createElement('script');
            recaptcha_script.src = "https://www.recaptcha.net/recaptcha/api.js?onload=onloadRecaptchaVisitorCheckCallback&render=explicit";
            recaptcha_script.async = true;
            recaptcha_script.defer = true;
            document.body.appendChild(recaptcha_script);

            var CheckVisitorScore;

            var onloadRecaptchaVisitorCheckCallback = function () {
                CheckVisitorScore = grecaptcha.render({
                    'sitekey': app_vars.recaptcha_v3_site_key,
                });

                grecaptcha.ready(function () {
                    grecaptcha.execute(CheckVisitorScore, {action: 'visitorCheck'}).then(function (token) {
                        visitorCheckForm.prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
                        visitorCheckForm.prepend('<input type="hidden" name="g-recaptcha-action" value="visitorCheck">');

                        visitorCheckProcess();
                    });
                });
            };
        } else {
            visitorCheckProcess();
        }
    </script>
@endif
