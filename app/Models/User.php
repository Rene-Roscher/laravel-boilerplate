<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use App\Models\Traits\HasOrganizations;
use App\Models\Traits\HasShortableUuid;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use HasUuids, HasShortableUuid;
    use TwoFactorAuthenticatable;
    use HasRoles;
    use HasMedia;
    use HasOrganizations;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $appends = [
        'two_factor_enabled', 'two_factor_pending', 'avatar_url',
    ];

    protected $with = [
        'currentOrganization', 'organizations'
    ];

    protected array $mediaFields = [
        'avatar' => [
            'path' => 'avatars',
        ],
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function twoFactorEnabled(): Attribute
    {
        return Attribute::make(
            get: fn () => ! is_null($this->two_factor_secret)
        );
    }

    public function twoFactorPending(): Attribute
    {
        return Attribute::make(
            get: fn () => Fortify::confirmsTwoFactorAuthentication() && ! is_null($this->two_factor_secret) && is_null($this->two_factor_confirmed_at)
        );
    }

    public function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getMediaUrl('avatar', 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF')
        );
    }

    public function twoFactorQrCodeSvg()
    {
        $qrSize = 192;
        $logoSize = 30;

        $centerX = $qrSize / 2;
        $centerY = $qrSize / 2;

        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle($qrSize, 0, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(45, 55, 72))),
                new SvgImageBackEnd
            )
        ))->writeString($this->twoFactorQrCodeUrl());

        $svg = trim(substr($svg, strpos($svg, "\n") + 1));

        $endSvgPos = strrpos($svg, '</svg>');
        $svgPrefix = substr($svg, 0, strpos($svg, '>') + 1);
        $svgContent = substr($svg, strpos($svg, '>') + 1, $endSvgPos - strpos($svg, '>') - 1);

        $logoX = $centerX - ($logoSize / 2);
        $logoY = $centerY - ($logoSize / 2);

        $bgSize = $logoSize + 10;
        $bgX = $centerX - ($bgSize / 2);
        $bgY = $centerY - ($bgSize / 2);

        $bgLayer = '<rect x="'.$bgX.'" y="'.$bgY.'" width="'.$bgSize.'" height="'.$bgSize.'" rx="12" ry="12" fill="currentColor"/>';

        $logoSvg = '<svg x="'.$logoX.'" y="'.$logoY.'" width="'.$logoSize.'" height="'.$logoSize.'" viewBox="0 0 37.77 47.31">
                        <path fill="#00e091" d="M107.13,27.12l-7.08,28.71a1,1,0,0,1-1.93,0l-1.59-6.41a1,1,0,0,0-1-.75H84.44a1,1,0,0,0-1,1.28l6.93,23a1,1,0,0,0,1,.71h15.07a1,1,0,0,0,1-.71l13.79-45.32a1,1,0,0,0-1-1.28H108.09A1,1,0,0,0,107.13,27.12Z" transform="translate(-83.45 -26.36)"/>
                    </svg>';

        return $svgPrefix .
            '<g id="qrCode">' . $svgContent . '</g>' .
            '<g id="logoLayer">' . $bgLayer . $logoSvg . '</g>' .
            '</svg>';
    }
}
