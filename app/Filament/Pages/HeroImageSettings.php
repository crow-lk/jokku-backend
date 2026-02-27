<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions as ActionsComponent;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form as FormComponent;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class HeroImageSettings extends Page
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';

    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Hero images';

    protected ?string $heading = 'Hero images settings';

    protected static bool $shouldRegisterNavigation = true;

    public function mount(): void
    {
        $this->form->fill($this->getInitialFormState());
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Hero images')
                ->schema($this->heroFields()),
        ]);
    }

    /**
     * @return array<Component>
     */
    protected function heroFields(): array
    {
        return [
            Forms\Components\FileUpload::make('image_paths')
                ->label('Desktop images')
                ->image()
                ->multiple()
                ->reorderable()
                ->disk('public')
                ->directory('hero-images')
                ->nullable()
                ->helperText('Recommended: 1920x1080 or larger.'),
            Forms\Components\FileUpload::make('mobile_image_paths')
                ->label('Mobile images')
                ->image()
                ->multiple()
                ->reorderable()
                ->disk('public')
                ->directory('hero-images/mobile')
                ->nullable()
                ->helperText('Recommended: 1080x1920 or larger.'),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                FormComponent::make([EmbeddedSchema::make('form')])
                    ->id('hero-image-settings-form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        ActionsComponent::make($this->getFormActions())
                            ->alignment($this->getFormActionsAlignment())
                            ->fullWidth($this->hasFullWidthFormActions())
                            ->sticky($this->areFormActionsSticky())
                            ->key('form-actions'),
                    ]),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save changes')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    public function getFormActionsAlignment(): string|Alignment
    {
        return Alignment::Start;
    }

    public function save(): void
    {
        $this->callHook('beforeValidate');

        $data = $this->form->getState();

        $this->callHook('afterValidate');

        $this->callHook('beforeSave');

        foreach ($this->getSettingMap() as $field => $settingKey) {
            $value = $data[$field] ?? null;

            if ($field === 'image_paths' || $field === 'mobile_image_paths') {
                $imagePaths = is_array($value) ? $value : [];
                $imagePaths = array_values(array_filter($imagePaths, fn ($path): bool => filled($path)));

                $encodedImagePaths = $imagePaths === [] ? null : json_encode($imagePaths);

                Setting::setValue($settingKey, $encodedImagePaths === false ? null : $encodedImagePaths);

                $legacyKey = match ($field) {
                    'image_paths' => 'hero.image_path',
                    'mobile_image_paths' => 'hero.mobile_image_path',
                };

                Setting::setValue($legacyKey, $imagePaths[0] ?? null);

                continue;
            }

            Setting::setValue($settingKey, blank($value) ? null : (string) $value);
        }

        $this->callHook('afterSave');

        Notification::make()
            ->title('Hero images updated')
            ->success()
            ->body('The hero images have been saved.')
            ->send();
    }

    /**
     * @return array<string, string>
     */
    protected function getSettingMap(): array
    {
        return [
            'image_paths' => 'hero.image_paths',
            'mobile_image_paths' => 'hero.mobile_image_paths',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getInitialFormState(): array
    {
        $state = [];

        foreach ($this->getSettingMap() as $field => $settingKey) {
            $value = Setting::getValue($settingKey);

            if ($field === 'image_paths') {
                $imagePaths = $this->normalizeImagePaths($value);

                if ($imagePaths === []) {
                    $legacyPath = Setting::getValue('hero.image_path');

                    if (filled($legacyPath)) {
                        $imagePaths = [$legacyPath];
                    }
                }

                $state[$field] = $imagePaths;

                continue;
            }

            if ($field === 'mobile_image_paths') {
                $imagePaths = $this->normalizeImagePaths($value);

                if ($imagePaths === []) {
                    $legacyPath = Setting::getValue('hero.mobile_image_path');

                    if (filled($legacyPath)) {
                        $imagePaths = [$legacyPath];
                    }
                }

                $state[$field] = $imagePaths;

                continue;
            }

            $state[$field] = $value;
        }

        return $state;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeImagePaths(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return array_values(array_filter($decoded, fn ($path): bool => filled($path)));
        }

        if (is_string($decoded)) {
            return [$decoded];
        }

        return [$value];
    }
}
