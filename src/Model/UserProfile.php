<?php

namespace Tgallice\FBMessenger\Model;

class UserProfile
{
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const PROFILE_PIC = 'profile_pic';
    const LOCALE = 'locale';
    const TIMEZONE = 'timezone';
    const GENDER = 'gender';

    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return null|string
     */
    public function getFirstName()
    {
        return $this->get(self::FIRST_NAME);
    }

    /**
     * @return null|string
     */
    public function getLastName()
    {
        return $this->get(self::LAST_NAME);
    }

    /**
     * @return null|string
     */
    public function getProfilePic()
    {
        return $this->get(self::PROFILE_PIC);
    }

    /**
     * @return null|string
     */
    public function getLocale()
    {
        return $this->get(self::LOCALE);
    }

    /**
     * @return null|string
     */
    public function getTimezone()
    {
        return $this->get(self::TIMEZONE);
    }

    /**
     * @return null|string
     */
    public function getGender()
    {
        return $this->get(self::GENDER);
    }

    private function get($index)
    {
        return isset($this->data[$index]) ? $this->data[$index] : null;
    }

    /**
     * @param array $data
     *
     * @return UserProfile
     */
    public static function create(array $data)
    {
        return new self($data);
    }
}
