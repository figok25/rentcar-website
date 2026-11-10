<?php

namespace App\Filament\Pages;

use App\Models\WebsiteSetting;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class ManageWebsiteSettings extends Page
{
    /** key pengaturan => grup. */
    public const KEYS = [
        'business_name' => 'general',
        'logo_path' => 'general',
        'whatsapp_number' => 'contact',
        'phone_number' => 'contact',
        'address' => 'contact',
        'maps_embed_url' => 'contact',
        'instagram_url' => 'social',
        'facebook_url' => 'social',
        'hero_title' => 'home',
        'hero_subtitle' => 'home',
        'hero_image_path' => 'home',
        'footer_description' => 'home',
        'feature_motor_enabled' => 'features',
        'feature_tours_enabled' => 'features',
        'feature_travel_enabled' => 'features',
    ];

    private const FILE_KEYS = ['logo_path', 'hero_image_path'];

    private const TOGGLE_KEYS = ['feature_motor_enabled', 'feature_tours_enabled', 'feature_travel_enabled'];

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Pengaturan website';

    protected static ?string $title = 'Pengaturan website';

    protected static ?string $slug = 'pengaturan-website';

    protected static ?int $navigationSort = 90;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->currentValues());
    }

    /**
     * @return array<string, mixed>
     */
    private function currentValues(): array
    {
        $values = [];

        foreach (self::KEYS as $key => $group) {
            $values[$key] = in_array($key, self::TOGGLE_KEYS, true)
                ? WebsiteSetting::bool($key)
                : WebsiteSetting::get($key);
        }

        return $values;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bisnis')
                    ->columns(2)
                    ->schema([
                        TextInput::make('business_name')
                            ->label('Nama bisnis')
                            ->required()
                            ->maxLength(120),
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('site')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Opsional. Jika kosong, nama bisnis tampil di dalam plat kuning.'),
                    ]),

                Section::make('Kontak')
                    ->columns(2)
                    ->schema([
                        TextInput::make('whatsapp_number')
                            ->label('Nomor WhatsApp admin')
                            ->required()
                            ->placeholder('081234567890')
                            ->helperText('Boleh diawali 0 atau +62. Disimpan sebagai 62xxxxxxxxxx. Semua tombol dan form WhatsApp memakai nomor ini.')
                            ->dehydrateStateUsing(fn (?string $state): ?string => self::normalizeWhatsApp($state))
                            ->rules([fn (): Closure => self::whatsAppRule()]),
                        TextInput::make('phone_number')
                            ->label('Telepon (opsional)')
                            ->tel()
                            ->maxLength(30),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Textarea::make('maps_embed_url')
                            ->label('Peta Google Maps (opsional)')
                            ->rows(2)
                            ->helperText('Di Google Maps: Bagikan > Sematkan peta > salin HTML. Bisa ditempel utuh; hanya alamat src yang disimpan.')
                            ->dehydrateStateUsing(fn (?string $state): ?string => self::extractMapsUrl($state))
                            ->rules([fn (): Closure => self::mapsRule()])
                            ->columnSpanFull(),
                    ]),

                Section::make('Sosial media')
                    ->columns(2)
                    ->schema([
                        TextInput::make('instagram_url')->label('Instagram')->url()->maxLength(255)->placeholder('https://instagram.com/akun'),
                        TextInput::make('facebook_url')->label('Facebook')->url()->maxLength(255)->placeholder('https://facebook.com/halaman'),
                    ]),

                Section::make('Beranda dan footer')
                    ->columns(2)
                    ->schema([
                        TextInput::make('hero_title')->label('Judul utama')->maxLength(120)->columnSpanFull(),
                        Textarea::make('hero_subtitle')->label('Teks pendukung')->rows(2)->maxLength(300)->columnSpanFull(),
                        FileUpload::make('hero_image_path')
                            ->label('Foto utama beranda')
                            ->image()
                            ->disk('public')
                            ->directory('site')
                            ->visibility('public')
                            ->maxSize(4096)
                            ->helperText('Opsional. Jika kosong, foto unit pilihan pertama dipakai.'),
                        Textarea::make('footer_description')->label('Deskripsi footer')->rows(3)->maxLength(300),
                    ]),

                Section::make('Fitur opsional')
                    ->description('Fitur yang dimatikan tidak muncul di menu dan halamannya memberi 404.')
                    ->schema([
                        Toggle::make('feature_motor_enabled')
                            ->label('Rental motor')
                            ->helperText('Aktifkan /rental-motor. Unit motor memakai kategori dengan slug "motor".'),
                        Toggle::make('feature_tours_enabled')
                            ->label('Paket wisata')
                            ->helperText('Aktifkan /paket-wisata. Menu tampil setelah ada paket yang dipublikasikan.'),
                        Toggle::make('feature_travel_enabled')
                            ->label('Layanan travel')
                            ->helperText('Aktifkan /travel. Menu tampil setelah ada layanan yang dipublikasikan.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Simpan pengaturan')
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach (self::KEYS as $key => $group) {
            $value = $state[$key] ?? null;

            if (in_array($key, self::FILE_KEYS, true)) {
                $value = is_array($value) ? (array_values($value)[0] ?? null) : $value;
                $this->discardReplacedFile($key, $value);
            }

            if (is_string($value)) {
                $value = trim($value);
            }

            WebsiteSetting::set($key, in_array($key, self::TOGGLE_KEYS, true) ? (bool) $value : ($value === '' ? null : $value), $group);
        }

        Notification::make()->title('Pengaturan disimpan')->success()->send();
    }

    /** Hapus file lama dari disk saat diganti atau dihapus admin. */
    private function discardReplacedFile(string $key, ?string $new): void
    {
        $old = WebsiteSetting::get($key);

        if ($old && $old !== $new && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
    }

    public static function normalizeWhatsApp(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            return null;
        }

        return str_starts_with($digits, '0') ? '62'.substr($digits, 1) : $digits;
    }

    private static function whatsAppRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $normalized = self::normalizeWhatsApp(is_string($value) ? $value : null);

            if ($normalized === null || ! preg_match('/^[1-9]\d{7,14}$/', $normalized)) {
                $fail('Nomor WhatsApp tidak valid. Contoh: 081234567890 atau 6281234567890.');
            }
        };
    }

    /** Terima HTML iframe utuh atau alamat src saja. */
    public static function extractMapsUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/src\s*=\s*"([^"]+)"/i', $value, $match)) {
            return html_entity_decode($match[1]);
        }

        return $value;
    }

    private static function mapsRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $url = self::extractMapsUrl(is_string($value) ? $value : null);

            if ($url !== null && ! str_starts_with($url, 'https://www.google.com/maps/embed')) {
                $fail('Peta harus berasal dari Google Maps (Bagikan > Sematkan peta).');
            }
        };
    }
}
