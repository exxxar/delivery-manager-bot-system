<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;

class SupplierJobController extends Controller
{
    /**
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function store(Request $request)
    {
        // 🔹 Валидация данных
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'reg_number'   => 'required|string|max:100',
            'email'        => 'required|email|max:255',
            'phone'        => 'required|string|max:50',
            'categories'   => 'nullable|string',
            'products'     => 'nullable|string',
            'volume'       => 'nullable|numeric|min:0',
            'terms'        => 'nullable|string',
            'consent'      => 'accepted'
        ]);

        // 🔹 Формируем текстовую переменную
        $text =
            "#заявка, #поставщик\n"
            . "📋 Заявка поставщика\n"
            . "Компания: {$validated['company_name']}\n"
            . "ИНН/рег. номер: {$validated['reg_number']}\n"
            . "Email: {$validated['email']}\n"
            . "Телефон: {$validated['phone']}\n"
            . "Категории товаров: " . ($validated['categories'] ?? '-') . "\n"
            . "Описание товаров: " . ($validated['products'] ?? '-') . "\n"
            . "Средний объём поставок: " . ($validated['volume'] ?? '-') . "\n"
            . "Условия сотрудничества: " . ($validated['terms'] ?? '-') . "\n";

        // 🔹 Проверка авторизации пользователя
        $user = $request->botUser ?? null;
        if (is_null($user)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, "Пользователь не авторизован");
        }

        // 🔹 Генерация Word‑документа из шаблона
        $template = new \PhpOffice\PhpWord\TemplateProcessor(storage_path('app/templates/Шаблон договора поставщика.docx'));
        $template->setValue('company_name', $validated['company_name']);
        $template->setValue('reg_number', $validated['reg_number']);
        $template->setValue('email', $validated['email']);
        $template->setValue('phone', $validated['phone']);
        $template->setValue('categories', $validated['categories'] ?? '-');
        $template->setValue('products', $validated['products'] ?? '-');
        $template->setValue('volume', $validated['volume'] ?? '-');
        $template->setValue('terms', $validated['terms'] ?? '-');

        // Дополнительные данные для договора
        $template->setValue('contract_number', 'SUP-' . date('YmdHis'));
        $template->setValue('contract_date', now()->format('d.m.Y'));
        $template->setValue('start_date', now()->addDays(7)->format('d.m.Y'));
        $template->setValue('end_date', now()->addYear()->format('d.m.Y'));

        $fileName = 'request_' .Carbon::now()->format("Y-m-d H-i-s"). '.docx';
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
       \Illuminate\Support\Facades\File::delete($path);

        // 🔹 Возвращаем JSON‑ответ
        return response()->json([
            'success' => true,
            'message' => 'Заявка успешно обработана',
            'text'    => $text
        ]);
    }
}
