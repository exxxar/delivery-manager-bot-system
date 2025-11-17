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

class AdminJobController extends Controller
{

    /**
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function generateApplicationDoc(array $validated)
    {
        // Загружаем шаблон
        $template = new TemplateProcessor(storage_path('app/templates/Шаблон договора администратора.docx'));

        // Подставляем данные
        $template->setValue('fio', $validated['fio']);
        $template->setValue('email', $validated['email']);
        $template->setValue('phone', $validated['phone']);
        $template->setValue('age', $validated['age']);
        $template->setValue('salary', $validated['salary']);
        $template->setValue('experience', $validated['experience'] ?? '-');
        $template->setValue('reason', $validated['reason'] ?? '-');

        // Сохраняем во временный файл
        $fileName = 'application_' . time() . '.docx';
        $path = storage_path("app/public/{$fileName}");
        $template->saveAs($path);

        // Отдаём файл пользователю
        return $path;
    }

    /**
     * @throws HttpException
     */
    public function store(Request $request)
    {
        // 🔹 Валидация данных
        $validated = $request->validate([
            'fio'       => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'phone'     => 'required|string|max:50',
            'age'       => 'required|integer|min:18|max:100',
            'salary'    => 'required|numeric|min:0',
            'experience'=> 'nullable|string',
            'reason'    => 'nullable|string',
            'consent'   => 'accepted'
        ]);

        // 🔹 Формируем текстовую переменную
        $text = "#заявка, #администратор\n"
            ."📋 Заявка на администратора\n"
            . "ФИО: {$validated['fio']}\n"
            . "Email: {$validated['email']}\n"
            . "Телефон: {$validated['phone']}\n"
            . "Возраст: {$validated['age']}\n"
            . "Зарплатные ожидания: {$validated['salary']} ₽\n"
            . "Опыт работы: " . ($validated['experience'] ?? '-') . "\n"
            . "Причина выбора позиции: " . ($validated['reason'] ?? '-') . "\n";

        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new HttpException("Пользователь не авторизован", 403);

        $path = $this->generateApplicationDoc($validated);

        $fileName = "request_".Carbon::now()->format("Y-m-d H-i-s").".docx";

        \App\Facades\BotMethods::bot()
            ->sendMessage($user->telegram_chat_id,$text . "\n\n<b>Заявка принята к рассмотрению</b>");

        sleep(1);

        \App\Facades\BotMethods::bot()
            ->sendDocument(env("TELEGRAM_ADMIN_CHANNEL"),$text,
                \Telegram\Bot\FileUpload\InputFile::create($path,$fileName));

        File::delete($path);

        return response()->json([
            'success' => true,
            'message' => 'Заявка успешно обработана',
            'text'    => $text
        ]);
    }
}
