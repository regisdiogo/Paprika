<?php
namespace helper;
if (!defined('PAPRIKA_LIBRARY_PATH')) die('Not allowed');

class HtmlHelper {

    const ELEMENT_VALUE = 'value';
    const ELEMENT_LINK = 'link';
    const ELEMENT_TYPE = 'type';
    const ELEMENT_ALIGN = 'align';

    const INPUT_TYPE_TEXT = 'text';
    const INPUT_TYPE_CHECKBOX = 'checkbox';
    const INPUT_TYPE_SUBMIT = 'submit';
    const INPUT_TYPE_BUTTON = 'button';
    const INPUT_TYPE_HIDDEN = 'hidden';

    const INPUT_FORMAT_DATE = 'date';
    const INPUT_FORMAT_NUMBER = 'number';
    const INPUT_FORMAT_DECIMAL = 'decimal';

    const SELECT = 'select';
    const RADIO = 'radio';
    const TEXTAREA = 'textarea';

    private static $htmlLink = '<a href="%2$s">%1$s</a>';
    private static $input = '<input type="%1$s" id="%2$s" name="%3$s" %4$s />';
    private static $inputValue = 'value="%1$s"';
    private static $inputMaxLength = 'maxlength="%1$s"';
    private static $inputStyleClass = 'class="%1$s"';
    private static $inputChecked = 'checked="checked"';
    private static $inputDisabled = 'disabled="disabled"';
    private static $label = '<label for="%2$s">%1$s</label>';
    private static $formParameters = ' action="%1$s" method="%2$s" name="%3$s" id="%3$s" %4$s ';
    private static $formOnSubmit = 'onSubmit="%1$s"';
    private static $selectHeader = '<select id="%1$s" name="%2$s" %4$s>%3$s</select>';
    private static $selectOption = '<option value="%1$s" %3$s>%2$s</option>';
    private static $selectSelected = 'selected="selected"';
    private static $radio = '<input type="radio" id="%1$s" name="%2$s" value="%3$s" %5$s><label for="%1$s">%4$s</label></input>';
    private static $textArea = '<textarea id="%1$s" name="%2$s" cols="%3$s" rows="%4$s" %6$s>%5$s</textarea>';

    public static function createTable($header, $content, $paginator = null) {
        $table = null;

        if ($header && $content) {
            $columns = 0;
            $table = '<table class="listTable">';
            $table .= '<tr>';
            foreach ($header as $cell) {
                $columns++;
                $cellValue = '';
                if (isset($cell[self::ELEMENT_LINK]) && strlen($cell[self::ELEMENT_LINK]) > 0) {
                    $cellValue = self::createLink($cell[self::ELEMENT_VALUE], $cell[self::ELEMENT_LINK]);
                } else {
                    $cellValue = isset($cell[self::ELEMENT_VALUE]) ? $cell[self::ELEMENT_VALUE] : '&nbsp;';
                }
                $table .= '<th>'.$cellValue.'</th>';
            }
            $table .= '</tr>';
            foreach ($content as $row) {
                $table .= '<tr>';
                foreach ($row as $cell) {
                    $cellValue = '';
                    if ($cell[self::ELEMENT_LINK]) {
                        $cellValue = self::createLink($cell[self::ELEMENT_VALUE], $cell[self::ELEMENT_LINK]);
                    } else if ($cell[self::ELEMENT_TYPE] == self::INPUT_TYPE_CHECKBOX) {
                        $cellValue = self::createInputDisabled(self::INPUT_TYPE_CHECKBOX, $cell[self::ELEMENT_VALUE]);
                    } else {
                        $cellValue = $cell[self::ELEMENT_VALUE];
                    }
                    if (StringHelper::isNotNull($cell[self::ELEMENT_ALIGN])) {
                        $table .= '<td align="'.$cell[self::ELEMENT_ALIGN].'">'.$cellValue.'</td>';
                    } else {
                        $table .= '<td>'.$cellValue.'</td>';
                    }
                }
                $table .= '</tr>';
            }
            if (isset($paginator) && count($paginator) > 1) {
                $table .= '<tr>';
                $table .= '<td colspan="'.$columns.'">';
                foreach ($paginator as $page=>$link) {
                    if ($link) {
                        $table .= self::createLink($page, $link);
                    } else {
                        $table .= $page;
                    }
                }
                $table .= '</td>';
                $table .= '</tr>';
            }
            $table .= "</table>";
        }

        return $table;
    }

    public static function createLink($text, $link) {
        return sprintf(self::$htmlLink, $text, $link);
    }

    public static function createInputDisabled($type, $value) {
        $params = array();
        if (strlen($value) > 0) {
            if ($type == self::INPUT_TYPE_CHECKBOX) {
                $params[] = sprintf(self::$inputValue, 1);
                if ($value) {
                    $params[] = self::$inputChecked;
                }
            } else {
                $params[] = sprintf(self::$inputValue, $value);
            }
        }
        if ($type == self::INPUT_TYPE_TEXT) {
            $params[] = sprintf(self::$inputStyleClass, $styleClass);
        }
        $params[] = self::$inputDisabled;
        return sprintf(self::$input, $type, '', '', implode(' ', $params));
    }

    public static function createTextArea($id, $name, $colsNum, $rowsNum, $value = null, $styleClass = null) {
        if (isset($styleClass))
            $styleClass = sprintf(self::$inputStyleClass, $styleClass);

        if (!isset($value))
            $value = '';

        return sprintf(self::$textArea, $id, $name, $colsNum, $rowsNum, $value, $styleClass);
    }

    public static function createInput($type, $id, $name, $value, $maxLength = null, $callback = null, $style = null) {
        $params = array();
        if (strlen($value) > 0) {
            if ($type == self::INPUT_TYPE_CHECKBOX) {
                //$hidden = self::createInput(self::INPUT_TYPE_HIDDEN, $id, $name, 0);
                $params[] = sprintf(self::$inputValue, 1);
                if ($value) {
                    $params[] = self::$inputChecked;
                }
            } else {
                $params[] = sprintf(self::$inputValue, $value);
            }

        } else if ($type == self::INPUT_TYPE_CHECKBOX) {
            $params[] = sprintf(self::$inputValue, 1);
        }
        if ($maxLength) {
            $params[] = sprintf(self::$inputMaxLength, $maxLength);
        }
        if (\helper\StringHelper::isNotNull($style)) {
            $params[] = sprintf(self::$inputStyleClass, $style);
        } else if ($type == self::INPUT_TYPE_TEXT) {
            $params[] = sprintf(self::$inputStyleClass, 'formText');
        }
        if ($callback) {
            $params[] = sprintf('onclick="%1$s"', $callback);
        }
        $input = sprintf(self::$input, $type, $id, $name, implode(' ', $params));
        if (!isset($hidden) || !$hidden) {
            return $input;
        } else {
            return $hidden.$input;
        }
    }

    public static function createLabel($value, $for) {
        return sprintf(self::$label, $value, $for);
    }

    public static function createFormParameters($id, $action, $method, $onSubmit = null) {
        $extras = array();
        if ($onSubmit) {
            $extras[] = sprintf(self::$formOnSubmit, $onSubmit);
        }
        return sprintf(self::$formParameters, $action, $method, $id, implode(' ', $extras));
    }

    public static function createSelect($id, $name, $optionsList, $selectedValue = null) {
        $options = array();
        $params = array();
        foreach ($optionsList as $key=>$item) {
            $extra = '';
            if ($key == $selectedValue) {
                $extra = self::$selectSelected;
            }
            $options[] = sprintf(self::$selectOption, $key, $item, $extra);
        }
        $params[] = sprintf(self::$inputStyleClass, 'formSelect');
        return sprintf(self::$selectHeader, $id, $name, implode('',$options), implode(' ', $params));
    }

    public static function createSelectDisabled($id, $name, $optionsList, $selectedValue = null) {
        $options = array();
        $params = array();
        $params[] = self::$inputDisabled;
        foreach ($optionsList as $key=>$item) {
            $extra = '';
            if ($key == $selectedValue) {
                $extra = self::$selectSelected;
            }
            $options[] = sprintf(self::$selectOption, $key, $item, $extra);
        }
        $params[] = sprintf(self::$inputStyleClass, 'formSelect');
        return sprintf(self::$selectHeader, $id, $name, implode('',$options), implode(' ', $params));
    }

    public static function createRadioList($id, $name, $optionsList, $selectedValue = null) {
        $options = array();
        $i = 1;
        foreach ($optionsList as $key=>$item) {
            $extra = '';
            if ($key == $selectedValue) {
                $extra = self::$inputChecked;
            }
            $options[] = $input = sprintf(self::$radio, $id.$i++, $name, $key, $item, $extra);
        }
        return implode(' ', $options);
    }
}
?>