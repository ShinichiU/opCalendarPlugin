<?php

class opGoogleOAuthJson
{
  const FILE_NAME = 'op_calendar_google_data_api_json_file';

  private $file;

  public function hasFile()
  {
    if (is_null($this->file))
    {
      $this->file = Doctrine_Core::getTable('File')->retrieveByFilename(self::FILE_NAME);
    }

    return (bool) $this->file;
  }

  public function getFile($isCreate = false)
  {
    if ($this->hasFile())
    {
      return $this->file;
    }

    if ($isCreate)
    {
      $file = new File;
      $file->setName(self::FILE_NAME);
      $file->setFileBin(new FileBin);

      return $file;
    }

    return false;
  }

  public function saveFile(sfValidatedFile $validatedFile)
  {
    $file = $this->getFile(true);
    $file->setType($validatedFile->getType());
    $file->setOriginalFilename($validatedFile->getOriginalName());
    $file->getFileBin()->setBin(file_get_contents($validatedFile->getTempName()));

    $file->save();
  }

  public function delete()
  {
    if ($file = $this->getFile())
    {
      $file->setFileBin(null);
      $file->delete();
    }
  }
}
