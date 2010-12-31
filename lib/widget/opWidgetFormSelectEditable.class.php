<?php

/**
 * opWidgetFormSelectEditable
 *
 * @package    OpenPNE
 * @subpackage widget
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
class opWidgetFormSelectEditable extends sfWidgetFormSelect
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * edit_mode:    A Boolean: true to enabled edit mode, false otherwise
   *  * with_delete:  Whether to add a delete checkbox or not
   *  * delete_label: The delete label used by the template
   *  * template:     The HTML template to use to render this widget when in edit mode
   *                  The available placeholders are:
   *                    * %input% (the image upload widget)
   *                    * %delete% (the delete checkbox)
   *                    * %delete_label% (the delete label text)
   *
   * In edit mode, this widget renders an additional widget named after the
   * file upload widget with a "_delete" suffix. So, when creating a form,
   * don't forget to add a validator for this additional field.
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormSelect
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addOption('edit_mode', true);
    $this->addOption('with_delete', true);
    $this->addOption('delete_label', 'remove this');
    $this->addOption('template', '%input%<br />%delete%&nbsp;%delete_label%');
  }

  /**
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $input = parent::render($name, $value, $attributes, $errors);

    if (!$this->getOption('edit_mode'))
    {
      return $input;
    }

    if ($this->getOption('with_delete'))
    {
      $deleteName = ']' == substr($name, -1) ? substr($name, 0, -1).'_delete]' : $name.'_delete';

      $delete = $this->renderTag('input', array_merge(array('type' => 'checkbox', 'name' => $deleteName), $attributes));
      $deleteLabel = $this->translate($this->getOption('delete_label'));
      $deleteLabel = $this->renderContentTag('label', $deleteLabel, array_merge(array('for' => $this->generateId($deleteName))));
    }
    else
    {
      $delete = '';
      $deleteLabel = '';
    }

    return strtr($this->getOption('template'), array('%input%' => $input, '%delete%' => $delete, '%delete_label%' => $deleteLabel));
  }
}
