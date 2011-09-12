<?php

/**
 * PluginSchedule form.
 *
 * @package    OpenPNE
 * @subpackage form
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
class MiniScheduleForm extends BaseForm
{
  private $params = array();

  public function configure()
  {
    $this->generateWeeks();

    $this->setWidget('title', new sfWidgetFormInput());
    $this->setWidget('start_date', new sfWidgetFormSelect(array('choices' => $this->params)));

    $this->validatorSchema['title'] = new opValidatorString(array('trim' => true, 'required' => true));
    $this->validatorSchema['start_date'] = new opValidatorDate(array('required' => true));
    $this->widgetSchema->setNameFormat('weekly_schedule[%s]');
  }

  private function generateWeeks()
  {
    $i18n = sfContext::getInstance()->getI18N();
    foreach ($this->getOption('calendar', array()) as $item)
    {
      $nowTime = sprintf('%04d-%02d-%02d', (int)$item['year'], (int)$item['month'], (int)$item['day']);
      $this->params[$nowTime] = sprintf('%d/%d(%s)', (int)$item['month'], (int)$item['day'], $i18n->__($item['dayofweek_item_name']));
      if ($item['today'])
      {
        $this->setDefault('start_date', $nowTime);
      }
    }
  }

  public function save($con = null)
  {
    $schedule = new Schedule();
    $schedule->setStartDate($this->getValue('start_date'));
    $schedule->setEndDate($this->getValue('start_date'));
    $schedule->setTitle($this->getValue('title'));
    $schedule->setBody('');
    $schedule->setMember(sfContext::getInstance()->getUser()->getMember());

    $scheduleMember = new ScheduleMember();
    $scheduleMember->setSchedule($schedule);
    $scheduleMember->setMember($schedule->Member);
    $scheduleMember->save($con);

    return $schedule->save($con);
  }
}
