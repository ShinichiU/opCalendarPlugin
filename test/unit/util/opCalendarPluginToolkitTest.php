<?php

include dirname(__FILE__).'/../../bootstrap/unit.php';

$t = new lime_test(12, new lime_output_color());

function check($period, $date = null)
{
  return opCalendarPluginToolkit::getMonthByPediod($period, 'Y-m', $date ? strtotime($date) : null);
}

$t->is(check(-1, '2015-01-01'), '2014-12', '-1 Check 2015-01-01 to 2014-12');
$t->is(check(1, '2015-01-01'), '2015-02', '+1 Check 2015-01-01 to 2015-02');
$t->is(check(-1, '2015-12-31'), '2015-11', '-1 Check 2015-12-31 to 2015-11');
$t->is(check(1, '2015-12-31'), '2016-01', '+1 Check 2015-12-31 to 2016-01');
$t->is(check(-1, '2014-03-31'), '2014-02', '-1 Check 2014-03-31 to 2014-02');
$t->is(check(1, '2014-03-31'), '2014-04', '+1 Check 2014-03-31 to 2014-04');

$t->is(check(-5, '2015-01-01'), '2014-08', '-5 Check 2015-01-01 to 2014-08');
$t->is(check(5, '2015-01-01'), '2015-06', '+5 Check 2015-01-01 to 2015-06');
$t->is(check(-12, '2015-12-31'), '2014-12', '-12 Check 2015-12-31 to 2014-12');
$t->is(check(12, '2015-12-31'), '2016-12', '+12 Check 2015-12-31 to 2016-12');
$t->is(check(-20, '2014-03-31'), '2012-07', '-20 Check 2014-03-31 to 2012-07');
$t->is(check(20, '2014-03-31'), '2015-11', '+20 Check 2014-03-31 to 2015-11');
