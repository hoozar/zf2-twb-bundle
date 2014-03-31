<?php

namespace TwbBundle\Form\View\Helper;

class TwbBundleFormRadio extends \Zend\Form\View\Helper\FormRadio {

    /**
     * Separator for checkbox elements
     * @var string
     */
    protected $separator = '</div><div class="%s">';

    /**
     * @var string
     */
    protected $checkboxFormat = '<div class="%s">%s</div>';

    /**
     * @see \Zend\Form\View\Helper\FormRadio::render()
     * @param \Zend\Form\ElementInterface $oElement
     * @return string
     */
    public function render(\Zend\Form\ElementInterface $oElement) {
        if ($oElement->getOption('disable-twb')) {
            $sSeparator = $this->separator;
            $this->separator = '';
            $sReturn = parent::render($oElement);
            $this->separator = $sSeparator;
            return $sReturn;
        }
        $wot = trim($oElement->getAttribute('wrapperOpenTag'));
        $wct = trim($oElement->getAttribute('wrapperClosingTag'));
        if ((strlen($wot)>0) && (strlen($wct)>0)) {
            $this->checkboxFormat = $wot . '%s' . $wct;
            $this->separator = $wct . $wot;
        }
        $noWrap = $oElement->getAttribute('wrapperOmitTag');
        if ($noWrap) {
            $this->checkboxFormat = '<span data-to-discard="%s"></span>%s';
            $this->separator = '';
        }
        return sprintf($this->checkboxFormat, $oElement->getAttribute('wrapperClass'), parent::render($oElement));
    }

    /**
     * @see \Zend\Form\View\Helper\FormMultiCheckbox::renderOptions()
     * @param \Zend\Form\Element\MultiCheckbox $oElement
     * @param array $aOptions
     * @param array $aSelectedOptions
     * @param array $aAttributes
     * @return string
     */
    protected function renderOptions(\Zend\Form\Element\MultiCheckbox $oElement, array $aOptions, array $aSelectedOptions, array $aAttributes) {
        $iIterator = 0;
        $aGlobalLabelAttributes = $oElement->getLabelAttributes()? : $this->labelAttributes;
        $sMarkup = '';
        $oLabelHelper = $this->getLabelHelper();
        foreach ($aOptions as $key => $aOptionspec) {
            if (is_scalar($aOptionspec)) {
                $aOptionspec = array('label' => $aOptionspec, 'value' => $key);
            }

            $iIterator++;
            if ($iIterator > 1 && array_key_exists('id', $aAttributes)) {
                unset($aAttributes['id']);
            }

            //Option attributes
            $aInputAttributes = $aAttributes;
            if (isset($aOptionspec['attributes'])) {
                $aInputAttributes = \Zend\Stdlib\ArrayUtils::merge($aInputAttributes, $aOptionspec['attributes']);
            }

            //Option value
            $aInputAttributes['value'] = isset($aOptionspec['value']) ? $aOptionspec['value'] : '';

            //Selected option
            if (in_array($aInputAttributes['value'], $aSelectedOptions)) {
                $aInputAttributes['checked'] = true;
            } elseif (isset($aOptionspec['selected'])) {
                $aInputAttributes['checked'] = !!$aOptionspec['selected'];
            } else {
                $aInputAttributes['checked'] = isset($aInputAttributes['selected']) && $aInputAttributes['type'] !== 'radio' && $aInputAttributes['selected'] != false;
            }

            //Disabled option
            if (isset($aOptionspec['disabled'])) {
                $aInputAttributes['disabled'] = !!$aOptionspec['disabled'];
            } else {
                $aInputAttributes['disabled'] = isset($aInputAttributes['disabled']) && $aInputAttributes['disabled'] != false;
            }

            //Render option
            $sOptionMarkup = sprintf('<input %s%s', $this->createAttributesString($aInputAttributes), $this->getInlineClosingBracket());

            //Option label
            $sLabel = isset($aOptionspec['label']) ? $aOptionspec['label'] : '';
            if ($sLabel) {
                $aLabelAttributes = $aGlobalLabelAttributes;
                if (isset($aOptionspec['label_attributes'])) {
                    $aLabelAttributes = isset($aLabelAttributes) ? array_merge($aLabelAttributes, $aOptionspec['label_attributes']) : $aOptionspec['label_attributes'];
                }
                if (null !== ($oTranslator = $this->getTranslator())) {
                    $sLabel = $oTranslator->translate($sLabel, $this->getTranslatorTextDomain());
                }

                $optAdd = $oElement->getOption('option_addition');
                $optAddVal = $oElement->getOption('option_addition_with_value');
                $optAddString = '';
                if ($optAddVal) {
                    $optAddString = sprintf($optAddVal, $key);
                } else {
                    if ($optAdd) {
                        $optAddString = $optAdd;
                    } else {
                        $optAddString = '';
                    }
                }
                switch ($this->getLabelPosition()) {
                    case self::LABEL_PREPEND:
                        $sOptionMarkup = sprintf($oLabelHelper->openTag($aLabelAttributes) . '%s%s%s' . $oLabelHelper->closeTag(), $this->getEscapeHtmlHelper()->__invoke($sLabel), $sOptionMarkup, $optAddString);
                        break;
                    case self::LABEL_APPEND:
                    default:
                        $sOptionMarkup = sprintf($oLabelHelper->openTag($aLabelAttributes) . '%s%s%s' . $oLabelHelper->closeTag(), $sOptionMarkup, $optAddString, $this->getEscapeHtmlHelper()->__invoke($sLabel));
                        break;
                }
                // now we are doing field injection.
                // it's stupid idea, but for now have no other :/
                if ($oElement->getOption('inject_input_html')) {
                    if ($oElement->getOption('inject_input_value') == $aOptionspec['value'])
                    $sOptionMarkup .= $oElement->getOption('inject_input_html');
                    //$sOptionMarkup .= $this->getInputHelper()->__invoke();
                };
            }
            $sMarkup .= ($sMarkup ? sprintf($this->getSeparator(), $oElement->getAttribute('wrapperClass')) : '') . $sOptionMarkup;
        }
        return $sMarkup;
    }

}
