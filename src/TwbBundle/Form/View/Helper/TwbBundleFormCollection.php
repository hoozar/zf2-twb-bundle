<?php

namespace TwbBundle\Form\View\Helper;

class TwbBundleFormCollection extends \Zend\Form\View\Helper\FormCollection {

    /**
     * @var string
     */
    private static $legendFormat = '<legend%s>%s</legend>';

    /**
     * @var string
     */
    private static $fieldsetFormat = '<fieldset%s>%s</fieldset>';

    /**
     * Attributes valid for the tag represented by this helper
     * @var array
     */
    protected $validTagAttributes = array(
        'disabled' => true
    );

    /**
     * Render a collection by iterating through all fieldsets and elements
     * @param \Zend\Form\ElementInterface $oElement
     * @return string
     */
    public function render(\Zend\Form\ElementInterface $oElement) {
        $oRenderer = $this->getView();
        if (!method_exists($oRenderer, 'plugin')) {
            return '';
        }

        $bShouldWrap = $this->shouldWrap;

        $sMarkup = '';
        $sElementLayout = $oElement->getOption('twb-layout');
        if ($oElement instanceof \IteratorAggregate) {
            $oElementHelper = $this->getElementHelper();
            $oFieldsetHelper = $this->getFieldsetHelper();

            foreach ($oElement->getIterator() as $oElementOrFieldset) {
                $aOptions = $oElementOrFieldset->getOptions();
                if ($sElementLayout && empty($aOptions['twb-layout'])) {
                    $aOptions['twb-layout'] = $sElementLayout;
                    $oElementOrFieldset->setOptions($aOptions);
                }

                if ($oElementOrFieldset instanceof \Zend\Form\FieldsetInterface) {
                    $sMarkup .= $oFieldsetHelper($oElementOrFieldset);
                } elseif ($oElementOrFieldset instanceof \Zend\Form\ElementInterface) {
                    $sMarkup .= $oElementHelper($oElementOrFieldset);
                }
            }
            if ($oElement instanceof \Zend\Form\Element\Collection && $oElement->shouldCreateTemplate()) {
                $sMarkup .= $this->renderTemplate($oElement);
            }
        }

        if ($bShouldWrap) {
            $attr = $oElement->getAttributes();
            if (array_key_exists('fieldswrap', $attr)) {
                $sMarkup = sprintf($attr['fieldswrap'], $sMarkup);
            }
            if (($sLabel = $oElement->getLabel())) {
                if (null !== ($oTranslator = $this->getTranslator())) {
                    $sLabel = $oTranslator->translate($sLabel, $this->getTranslatorTextDomain());
                }

                if ( ! ($lf = $oElement->getAttribute('legendFormat')) ) {
                    $lf = self::$legendFormat;
                }
                $sMarkup = sprintf(
                        $lf, ($sAttributes = $this->createAttributesString($oElement->getLabelAttributes()? : array())) ? ' ' . $sAttributes : '', $this->getEscapeHtmlHelper()->__invoke($sLabel)
                    ) . $sMarkup;
                /*
                $sMarkup = sprintf(
                                self::$legendFormat, ($sAttributes = $this->createAttributesString($oElement->getLabelAttributes()? : array())) ? ' ' . $sAttributes : '', $this->getEscapeHtmlHelper()->__invoke($sLabel)
                        ) . $sMarkup;
                */
            }

            //Set form layout class
            if ($sElementLayout) {
                //$sLayoutClass = 'form-' . $sElementLayout;
                $sLayoutClass = '';
                if ($sElementClass = $oElement->getAttribute('class')) {
                    if (!preg_match('/(\s|^)' . preg_quote($sLayoutClass, '/') . '(\s|$)/', $sElementClass)) {
                        $oElement->setAttribute('class', trim($sElementClass . ' ' . $sLayoutClass));
                    }
                } else {
                    $oElement->setAttribute('class', $sLayoutClass);
                }
            }

            //$sMarkup = sprintf(
            //    self::$fieldsetFormat, ($sAttributes = $this->createAttributesString($oElement->getAttributes())) ? ' ' . $sAttributes : '', $sMarkup
            //);
            $sMarkup = sprintf(
                ($ffs = $oElement->getOption('fieldset-format-string')) ? $ffs : self::$fieldsetFormat, ($sAttributes = $this->createAttributesString($oElement->getAttributes())) ? ' ' . $sAttributes : '', $sMarkup
            );
        }
        return $sMarkup;
    }

}
