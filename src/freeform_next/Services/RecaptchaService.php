<?php

namespace Solspace\Addons\FreeformNext\Services;

use GuzzleHttp\Client;
use Solspace\Addons\FreeformNext\Library\Composer\Components\Form;
use Solspace\Addons\FreeformNext\Library\DataObjects\FormRenderObject;
use Solspace\Addons\FreeformNext\Library\Session\Honeypot;
use Solspace\Addons\FreeformNext\Model\SpamReasonModel;
use Solspace\Addons\FreeformNext\Repositories\SettingsRepository;

class RecaptchaService
{
    /**
     * Adds Recaptcha javascript to forms
     *
     * @param FormRenderObject $renderObject
     */
    public function addRecaptchaJavascriptToForm(FormRenderObject $renderObject): void
    {
        $settingsModel = $this->getSettingsService()->getSettingsModel();

        $isRecaptchaEnabled = $settingsModel->isRecaptchaEnabled();
        $isRecaptchaV3 = $settingsModel->getRecaptchaType() === 'v3';
        $recaptchaKey = $settingsModel->getRecaptchaKey();
        $recaptchaSecret = $settingsModel->getRecaptchaSecret();

        if (!$isRecaptchaEnabled) {
            return;
        }

        if (!$isRecaptchaV3) {
            return;
        }

        if (!$recaptchaKey) {
            return;
        }

        if (!$recaptchaSecret) {
            return;
        }

        $renderObject->appendToOutput($this->getRecaptchaJavascript($renderObject->getForm()));
    }

    /**
     * Assembles a Recaptcha field
     *
     * @param FormRenderObject $renderObject
     */
    public function addRecaptchaInputToForm(FormRenderObject $renderObject): void
    {
        $settingsModel = $this->getSettingsService()->getSettingsModel();

        $isRecaptchaEnabled = $settingsModel->isRecaptchaEnabled();
        $isRecaptchaV3 = $settingsModel->getRecaptchaType() === 'v3';
        $recaptchaKey = $settingsModel->getRecaptchaKey();
        $recaptchaSecret = $settingsModel->getRecaptchaSecret();

        if (!$isRecaptchaEnabled) {
            return;
        }

        if (!$isRecaptchaV3) {
            return;
        }

        if (!$recaptchaKey) {
            return;
        }

        if (!$recaptchaSecret) {
            return;
        }

        $renderObject->appendToOutput($this->getRecaptchaInput());
    }

    /**
     * @param Form $form
     */
    public function validateFormRecaptcha(Form $form): void
    {
        $settingsModel = $this->getSettingsService()->getSettingsModel();

        $isRecaptchaEnabled = $settingsModel->isRecaptchaEnabled();
        $isRecaptchaV3 = $settingsModel->getRecaptchaType() === 'v3';
        $recaptchaKey = $settingsModel->getRecaptchaKey();
        $recaptchaSecret = $settingsModel->getRecaptchaSecret();

        if (!$isRecaptchaEnabled) {
            return;
        }

        if (!$isRecaptchaV3) {
            return;
        }

        if (!$recaptchaKey) {
            return;
        }

        if (!$recaptchaSecret) {
            return;
        }

        $score = null;
        $spamReasonMessage = lang('Please verify that you are not a robot.');

        $response = ee()->input->post('g-recaptcha-response');

        if (!$response) {
            if (!$this->getSettingsService()->getSettingsModel()->spamBlockLikeSuccessfulPost) {
                $form->addError($spamReasonMessage);
            }
        } else {
            $client = new Client();

            $postResponse = $client->post(
                'https://www.google.com/recaptcha/api/siteverify',
                [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'form_params' => [
                        'secret' => $recaptchaSecret,
                        'response' => $response,
                    ],
                ]
            );

            $result = json_decode((string) $postResponse->getBody(), true);

            if (isset($result['score'])) {
                $score = $result['score'];

                $minScore = $settingsModel->getRecaptchaScoreThreshold();

                $minScore = min(1, $minScore);
                $minScore = max(0, $minScore);

                if ($score < $minScore) {
                    $spamReasonMessage = lang('Spam test failed.');

                    // $form->addError(lang('Score check failed with ['.$score.']'));
                    if (!$this->getSettingsService()->getSettingsModel()->spamBlockLikeSuccessfulPost) {
                        $form->addError($spamReasonMessage);
                    }
                }
            }

            if ($result['success']) {
                return;
            }

            $errors = [];
            $errorCodes = $result['error-codes'];

            if (\in_array('missing-input-secret', $errorCodes, true)) {
                $errors[] = lang('The secret parameter is missing.');
            }

            if (\in_array('invalid-keys', $errorCodes, true)) {
                $errors[] = lang('The key parameter is invalid or malformed.');
            }

            if (\in_array('invalid-input-secret', $errorCodes, true)) {
                $errors[] = lang('The secret parameter is invalid or malformed.');
            }

            if (\in_array('missing-input-response', $errorCodes, true)) {
                $errors[] = lang('The response parameter is missing.');
            }

            if (\in_array('invalid-input-response', $errorCodes, true)) {
                $errors[] = lang('The response parameter is invalid or malformed.');
            }

            if (\in_array('bad-request', $errorCodes, true)) {
                $errors[] = lang('The request is invalid or malformed.');
            }

            if (\in_array('timeout-or-duplicate', $errorCodes, true)) {
                $errors[] = lang('The response is no longer valid: either is too old or has been used previously.');
            }
        }

        if (empty($errors)) {
            return;
        }

        if (!$this->getSettingsService()->getSettingsModel()->spamBlockLikeSuccessfulPost) {
            $form->addErrors($errors);

            $spamReasonMessage = implode(',', $errors);
        }

        $form->setMarkedAsSpam(SpamReasonModel::TYPE_CAPTCHA, 'reCaptcha - '.$spamReasonMessage, $score);
    }

    /**
     * @param Form $form
     * @return string
     */
    public function getRecaptchaJavascript(Form $form): string
    {
        $recaptchaKey = (string) ($this->getSettingsService()->getSettingsModel()->getRecaptchaKey() ?? '');

        // If there is no key, don't emit any JS
        if ($recaptchaKey === '') {
            return '';
        }

        // Safe JS literal for the key
        $siteKeyJS = json_encode($recaptchaKey, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        $template = <<<'JS'
<script>
(function() {
  if (window._freeformRecaptchaInit) {
      return;
  }

  window._freeformRecaptchaInit = true;

  const SITE_KEY = %s;

  function loadRecaptchaOnce() {
    if (document.getElementById('recaptcha-v3-loaded')) {
        return;
    }
    
    const script = document.createElement('script');
    script.id = 'recaptcha-v3-loaded';
    script.src = 'https://www.google.com/recaptcha/api.js?render=' + SITE_KEY;

    document.head.appendChild(script);
  }

  function ensureTarget(form) {
    let element = form.querySelector('#g-recaptcha-response');
    if (!element) {
      element = document.createElement('textarea');
      element.id = 'g-recaptcha-response';
      element.name = 'g-recaptcha-response';
      element.style.cssText = 'visibility:hidden;position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;border:none;';

      form.appendChild(element);
    }

    return element;
  }

  function bindForm(form) {
    if (!form || form.dataset.recaptchaBound === 'true') {
        return;
    }

    form.dataset.recaptchaBound = 'true';

    const target = ensureTarget(form);

    form.addEventListener('submit', event => {
      event.preventDefault();

      const execute = () => {
        if (!window.grecaptcha || !grecaptcha.execute) {
            setTimeout(execute, 50);
            
            return;
        }

        grecaptcha.ready(() => {
          grecaptcha.execute(SITE_KEY, { action: 'submit' })
            .then(token => {
                target.value = token;

                form.submit();
            })
            .catch(() => form.submit());
        });
      };

      execute();
    }, { passive: false });
  }

  function bindAllForms() {
    document.querySelectorAll('form').forEach(form => {
      // Freeform marker present in your HTML
      if (form.querySelector('input[name="formHash"]')) {
          bindForm(form);
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    loadRecaptchaOnce();

    bindAllForms();
  });

  // Bind dynamically-added forms too
  new MutationObserver(bindAllForms).observe(document.documentElement, { childList: true, subtree: true });
})();
</script>
JS;

        // Insert the JS-safe key literal into the template
        return sprintf($template, $siteKeyJS);
    }

    /**
     * @return string
     */
    public function getRecaptchaInput(): string
    {
        return '<textarea data-recaptcha="" id="g-recaptcha-response" name="g-recaptcha-response" style="visibility: hidden; position: absolute; top: -9999px; left: -9999px; width: 1px; height: 1px; overflow: hidden; border: none;"></textarea>';
    }

    /**
     * @return SettingsService
     */
    private function getSettingsService(): SettingsService
    {
        return new SettingsService();
    }
}
