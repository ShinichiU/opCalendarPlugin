<?php
abstract class opCalendarApiResults implements opCalendarApiResultsInterface, ArrayAccess
{
  protected
    $list = array(),
    $code = null,
    $result;

  public function is200StatusCode()
  {
    return 200 == $this->code;
  }

  public function setHttpStatusCode($code)
  {
    $this->code = $code;
  }

  public function setResult($result)
  {
    $this->result = $result;
  }

  public function parse()
  {
    throw RuntimeException('It need use parse() function in sub class.');
  }

  public function reset()
  {
    $this->list = array();
  }

  public function get($key = null)
  {
    return $this->list[$key];
  }

  public function offsetExists($offset)
  {
    return isset($this->list[$offset]);
  }

  public function offsetGet($offset)
  {
    return isset($this->list[$offset]) ? $this->list[$offset] : null;
  }

  public function offsetSet($offset, $value)
  {
    $this->list[$offset] = $value;
  }

  public function offsetUnset($offset)
  {
    unset($this->list[$offset]);
  }
}
