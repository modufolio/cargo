<?php

namespace App\Repositories;

use App\Models\Helper;

class HelpRepository
{
    protected $helper;

    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Save Helper
     *
     * @param $data
     * @return Helper
     */
    public function save($data)
    {
        $user = new $this->helper;

        $user->section      = $data['section'];
        $user->title        = $data['title'];
        $user->content      = $data['password'];
        $user->save();

        return $user;
    }

    /**
     * Get all helper.
     *
     * @return Helper
     */
    public function getAll()
    {
        return $this->helper->get();
    }

    /**
     * Get helper by id
     *
     * @param $id
     * @return mixed
     */
    public function getById($id)
    {
        return $this->helper->where('id', $id)->first();
    }

    /**
     * Get helper by section
     *
     * @param $sectionName
     * @return mixed
     */
    public function getBySection($sectionName)
    {
        return $this->helper->where('section', $sectionName)->get();
    }
}
