<?php

namespace App\Models;

use CodeIgniter\Model;

class UserTranslationModel extends Model
{
    protected $table         = 'user_translations';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'user_id', 'language', 'name'
    ];

    protected $validationRules = [
        'user_id'   => 'required|integer|is_not_unique[users.id]',
        'language'  => 'required|in_list[en,ur,zh]',
        'name'      => 'required|min_length[2]|max_length[100]',
    ];

    /**
     * Get all translations for a user
     */
    public function getByUserId(int $userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * Get translation for user by language
     */
    public function getByUserIdAndLanguage(int $userId, string $language)
    {
        return $this->where('user_id', $userId)
                    ->where('language', $language)
                    ->first();
    }

    /**
     * Get translations for multiple users indexed by user_id
     */
    public function getTranslationsByUserIds(array $userIds, string $language = null)
    {
        $query = $this->whereIn('user_id', $userIds);
        if ($language) {
            $query->where('language', $language);
        }
        $results = $query->findAll();

        $indexed = [];
        foreach ($results as $translation) {
            if (!isset($indexed[$translation->user_id])) {
                $indexed[$translation->user_id] = [];
            }
            $indexed[$translation->user_id][$translation->language] = $translation->name;
        }
        return $indexed;
    }
}
