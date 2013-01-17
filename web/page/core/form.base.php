<form <?=$c[presentation\BasePresentation::FORM_PARAMS]?>>

    <?php if ($c[presentation\BasePresentation::FORM_ELEMENTS]) { ?>
        <table class="input-form">
            <?php foreach ($c[presentation\BasePresentation::FORM_ELEMENTS] as $element) { ?>
                <tr>
                    <td class="form-label">
                        <?=$element[presentation\BasePresentation::FORM_LABEL]?>
                    </td>
                    <td class="form-element">
                        <?=$element[presentation\BasePresentation::FORM_INPUT]?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td colspan="2" style="text-align: right;" class="form-submit-area">
                    <?=$c[presentation\BasePresentation::FORM_CANCEL]?>
                    <?=$c[presentation\BasePresentation::FORM_SUBMIT]?>
                </td>
            </tr>
        </table>
    <?php } ?>

</form>