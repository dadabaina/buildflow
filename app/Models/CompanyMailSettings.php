<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class CompanyMailSettings extends Model
{
    protected $fillable = [
        'company_id', 'is_enabled', 'host', 'port', 'username',
        'password', 'encryption', 'from_address', 'from_name',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'port'       => 'integer',
        'password'   => 'encrypted',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Enregistre dynamiquement un mailer SMTP nommé "company_{id}" à partir de ces
     * réglages et renvoie son nom, prêt à être utilisé via Mail::mailer($nom).
     * Renvoie null si le SMTP personnalisé n'est pas activé/configuré : dans ce cas,
     * l'appelant doit utiliser le mailer par défaut (MAIL_MAILER du .env).
     */
    public function resolveMailerName(): ?string
    {
        if (!$this->is_enabled || !$this->host) {
            return null;
        }

        $name = 'company_' . $this->company_id;

        Config::set("mail.mailers.{$name}", [
            'transport'    => 'smtp',
            'host'         => $this->host,
            'port'         => $this->port ?: 587,
            'encryption'   => $this->encryption ?: null,
            'username'     => $this->username,
            'password'     => $this->password,
            'timeout'      => null,
        ]);

        return $name;
    }
}
