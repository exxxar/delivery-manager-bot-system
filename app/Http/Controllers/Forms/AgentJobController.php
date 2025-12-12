<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use HttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\TemplateProcessor;

class AgentJobController extends Controller
{
    /**
     * @throws HttpException
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function store(Request $request)
    {
        // 🔹 Валидация данных
        $validated = $request->validate([
            'fio'        => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'required|string|max:50',
            'age'        => 'required|integer|min:18|max:100',
            'salary'     => 'required|numeric|min:0',
            'experience' => 'nullable|string',
            'skills'     => 'nullable|string',
            'reason'     => 'nullable|string',
            'consent'    => 'accepted'
        ]);

        // 🔹 Формируем текстовую переменную
        $text = "#заявка, #младший_администратор\n"
            ."📋 Заявка на младшего администратора\n"
            . "ФИО: {$validated['fio']}\n"
            . "Email: {$validated['email']}\n"
            . "Телефон: {$validated['phone']}\n"
            . "Возраст: {$validated['age']}\n"
            . "Зарплатные ожидания: {$validated['salary']} ₽\n"
            . "Опыт работы: " . ($validated['experience'] ?? '-') . "\n"
            . "Навыки переговоров: " . ($validated['skills'] ?? '-') . "\n"
            . "Причина выбора позиции: " . ($validated['reason'] ?? '-') . "\n";

        // 🔹 Проверка авторизации пользователя
        $user = $request->botUser ?? null;
        if (is_null($user)) {
            throw new HttpException(403, "Пользователь не авторизован");
        }

        // 🔹 Генерация Word‑документа из шаблона
        $template = new TemplateProcessor(storage_path('app/templates/Шаблон договора агента.docx'));
        $template->setValue('fio', $validated['fio']);
        $template->setValue('email', $validated['email']);
        $template->setValue('phone', $validated['phone']);
        $template->setValue('age', $validated['age']);
        $template->setValue('salary', $validated['salary']);
        $template->setValue('experience', $validated['experience'] ?? '-');
        $template->setValue('skills', $validated['skills'] ?? '-');
        $template->setValue('reason', $validated['reason'] ?? '-');

        // 🔹 Дополнительные данные для договора
        $template->setValue('contract_number', 'CN-' . date('YmdHis'));
        $template->setValue('contract_date', now()->format('d.m.Y'));
        $template->setValue('position', 'Младший администратор'); // или Администратор/Поставщик/Клиент
        $template->setValue('company_name', 'ООО "Пример"');
        $template->setValue('company_address', 'г. Киев, ул. Примерная, 10');
        $template->setValue('start_date', now()->addDays(7)->format('d.m.Y'));
        $template->setValue('end_date', now()->addYear()->format('d.m.Y'));


        $fileName = 'request_' .Carbon::now()->format("Y-m-d H-i-s") . '.docx';
        $path = storage_path("app/public/{$fileName}");
        $template->saveAs($path);

        // 🔹 Отправка в Telegram
        \App\Facades\BotMethods::bot()
            ->sendMessage($user->telegram_chat_id, $text . "\n\n<b>Заявка принята к рассмотрению</b>");

        sleep(1);

        \App\Facades\BotMethods::bot()
            ->sendDocument(env("TELEGRAM_ADMIN_CHANNEL"), $text,
                \Telegram\Bot\FileUpload\InputFile::create($path, $fileName));

        // 🔹 Удаляем временный файл
        File::delete($path);

        // 🔹 Возвращаем JSON‑ответ
        return response()->json([
            'success' => true,
            'message' => 'Заявка успешно обработана',
            'text'    => $text
        ]);
    }
}
