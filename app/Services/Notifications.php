<?php

declare(strict_types=1);

// Безопасная имитация портальных и почтовых уведомлений без внешней отправки.

namespace App\Services;

use App\Support\DemoData;

final class Notifications
{
    public function __construct(
        private DemoData $state,
        private EmployeeDirectory $directory,
        private Birthdays $birthdays,
    ) {
    }

    public function all(): array
    {
        $rows = $this->state->get('notifications', []);
        usort($rows, static fn(array $a, array $b): int => strcmp((string) $b['created_at'], (string) $a['created_at']));
        return $rows;
    }

    public function add(string $type, string $channel, int $recipientId, string $messageRu, string $messageEn, string $status = 'success'): array
    {
        $rows = $this->state->get('notifications', []);
        $id = $this->nextId($rows);
        $row = [
            'id' => $id,
            'created_at' => (new \DateTimeImmutable('now'))->format(DATE_ATOM),
            'type' => $type,
            'channel' => $channel,
            'recipient_id' => $recipientId,
            'message_ru' => $messageRu,
            'message_en' => $messageEn,
            'status' => $status,
        ];
        $rows[] = $row;
        $this->state->set('notifications', $rows);
        return $row;
    }

    public function congratulate(int $employeeId, string $message = '', string $card = '', string $sticker = ''): array
    {
        $employee = $this->directory->find($employeeId);
        if (!$employee) {
            return [];
        }
        $nameRu = $this->directory->localized($employee, 'name', 'ru');
        $nameEn = $this->directory->localized($employee, 'name', 'en');
        $channels = $this->state->get('channels', ['portal' => true, 'email' => true]);
        $created = [];
        $message = trim($message);
        $detailsRu = $message !== '' ? ' Текст: «' . $message . '».' : '';
        $detailsEn = $message !== '' ? ' Message: “' . $message . '”.' : '';
        $mediaRu = ($card !== '' || $sticker !== '') ? ' Выбраны открытка и стикер.' : '';
        $mediaEn = ($card !== '' || $sticker !== '') ? ' A card and sticker were selected.' : '';
        if (!empty($channels['portal'])) {
            $created[] = $this->add('congratulation', 'portal', $employeeId, 'Поздравление для ' . $nameRu . ' добавлено в корпоративный чат.' . $mediaRu . $detailsRu, 'Congratulations for ' . $nameEn . ' were added to the corporate chat.' . $mediaEn . $detailsEn);
        }
        if (!empty($channels['email'])) {
            $created[] = $this->add('congratulation', 'email', $employeeId, 'Письмо с поздравлением для ' . $nameRu . ' подготовлено к отправке.' . $detailsRu, 'A congratulation email for ' . $nameEn . ' was prepared.' . $detailsEn);
        }

        if ($created !== []) {
            // Статус нужен только внутри демо: на главной сразу видно, кого уже поздравили.
            $this->birthdays->markCongratulated($employeeId);
        }

        return $created;
    }

    public function generateBirthdayReminders(): array
    {
        $created = [];
        $channels = $this->state->get('channels', ['portal' => true, 'email' => true]);
        foreach ($this->birthdays->upcoming(7, 'ru') as $row) {
            $reminder = $this->birthdays->effectiveReminder((int) $row['id']);
            if ($reminder === null || (int) $row['_days'] !== $reminder) {
                continue;
            }
            $nameRu = $this->directory->localized($row, 'name', 'ru');
            $nameEn = $this->directory->localized($row, 'name', 'en');
            if (!empty($channels['portal'])) {
                $created[] = $this->add('birthday_reminder', 'portal', 1, 'Напоминание: день рождения у ' . $nameRu . ' через ' . $row['_days'] . ' дн.', 'Reminder: ' . $nameEn . ' has a birthday in ' . $row['_days'] . ' day(s).');
            }
            if (!empty($channels['email'])) {
                $created[] = $this->add('birthday_reminder', 'email', 1, 'Письмо-напоминание о дне рождения ' . $nameRu . ' подготовлено.', 'A birthday reminder email for ' . $nameEn . ' was prepared.');
            }
        }
        return $created;
    }

    public function setChannels(bool $portal, bool $email): void
    {
        $this->state->set('channels', ['portal' => $portal, 'email' => $email]);
    }

    private function nextId(array $rows): int
    {
        $max = 0;
        foreach ($rows as $row) {
            $max = max($max, (int) ($row['id'] ?? 0));
        }
        return $max + 1;
    }
}
