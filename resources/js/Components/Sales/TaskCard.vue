<template>
    <article class="task-card">
        <header class="task-card__header">
            <div class="task-card__title-wrap">
                <span class="task-card__icon" aria-hidden="true">
                    <i class="fa-solid fa-clipboard-list"></i>
                </span>
                <div class="min-w-0">
                    <div class="task-card__eyebrow">Задание #{{ task.id }}</div>
                    <h3 class="task-card__title">{{ task.title || 'Без названия' }}</h3>
                </div>
            </div>
            <span class="task-status" :class="statusClass(task.status)">
                <span class="task-status__dot"></span>
                {{ statusLabels[task.status] || 'Неизвестный статус' }}
            </span>
        </header>

        <nav class="task-card__tabs" aria-label="Разделы сделки">
            <button type="button" class="task-card__tab" :class="{ 'is-active': activeTab === 'info' }" @click="activeTab = 'info'">
                <i class="fa-solid fa-circle-info"></i> Основная информация
            </button>
            <button type="button" class="task-card__tab" :class="{ 'is-active': activeTab === 'comments' }" @click="openCommentsTab">
                <i class="fa-regular fa-comments"></i> Комментарии
                <span class="task-card__tab-count">{{ comments.length }}</span>
            </button>
        </nav>

        <div v-show="activeTab === 'info'" class="task-card__tab-pane">
            <p v-if="task.description" class="task-card__description">
                {{ task.description }}
            </p>

            <section class="task-card__section" aria-label="Детали задания">
                <h4 class="task-card__section-title">
                    <i class="fa-regular fa-calendar me-2"></i>Даты и условия
                </h4>
                <div class="task-details-grid">
                    <div class="task-detail">
                        <span class="task-detail__icon"><i class="fa-regular fa-handshake"></i></span>
                        <div class="task-detail__body">
                            <span class="task-detail__label">Дата встречи</span>
                            <strong class="task-detail__value">{{ formatDate(task.due_date) }}</strong>
                        </div>
                    </div>
                    <div class="task-detail">
                        <span class="task-detail__icon"><i class="fa-solid fa-bag-shopping"></i></span>
                        <div class="task-detail__body">
                            <span class="task-detail__label">Дата продажи</span>
                            <strong class="task-detail__value">{{ formatDate(task.sale_date) }}</strong>
                        </div>
                    </div>
                    <div class="task-detail">
                        <span class="task-detail__icon"><i class="fa-solid fa-truck-fast"></i></span>
                        <div class="task-detail__body">
                            <span class="task-detail__label">Фактическая доставка</span>
                            <strong class="task-detail__value">{{ formatDate(task.actual_delivery_date) }}</strong>
                        </div>
                    </div>
                    <div class="task-detail">
                        <span class="task-detail__icon"><i class="fa-solid fa-wallet"></i></span>
                        <div class="task-detail__body">
                            <span class="task-detail__label">Тип оплаты</span>
                            <strong class="task-detail__value">
                                {{ task.payment_type === 0 || task.payment_type === '0' ? 'Наличный расчёт' : task.payment_type === 1 || task.payment_type === '1' ? 'Безналичный расчёт' : '—' }}
                            </strong>
                        </div>
                    </div>
                    <div class="task-detail">
                        <span class="task-detail__icon"><i class="fa-solid fa-boxes-stacked"></i></span>
                        <div class="task-detail__body">
                            <span class="task-detail__label">Количество</span>
                            <strong class="task-detail__value">{{ task.quantity ?? '—' }}</strong>
                        </div>
                    </div>
                    <div class="task-detail task-detail--price">
                        <span class="task-detail__icon"><i class="fa-solid fa-coins"></i></span>
                        <div class="task-detail__body">
                            <span class="task-detail__label">Сумма заказа</span>
                            <strong class="task-detail__value">{{ formatPrice(task.total_price) }} ₴</strong>
                        </div>
                    </div>
                </div>
            </section>

            <section class="task-card__section task-card__related" aria-label="Связанные элементы">
                <h4 class="task-card__section-title">
                    <i class="fa-solid fa-link me-2"></i>Связанные элементы
                </h4>
                <div class="task-related-list">
                    <div class="task-related-item">
                        <span class="task-related-item__label"><i class="fa-solid fa-user-tie"></i> Ответственный</span>
                        <strong>{{ task.agent?.name || '—' }}</strong>
                    </div>
                    <div v-if="task.customer" class="task-related-item">
                        <span class="task-related-item__label"><i class="fa-solid fa-user"></i> Покупатель</span>
                        <strong>{{ task.customer?.name || '—' }}</strong>
                    </div>
                    <div class="task-related-item">
                        <span class="task-related-item__label"><i class="fa-solid fa-truck"></i> Поставщик</span>
                        <strong>{{ task.supplier?.name || '—' }}</strong>
                    </div>
                    <div class="task-related-item">
                        <span class="task-related-item__label"><i class="fa-solid fa-box"></i> Продукт</span>
                        <strong>{{ task.product?.name || '—' }}</strong>
                    </div>
                    <div v-if="task.category" class="task-related-item">
                        <span class="task-related-item__label"><i class="fa-solid fa-tags"></i> Категория</span>
                        <strong>{{ task.category?.name || '—' }}</strong>
                    </div>
                    <div v-if="task.creator" class="task-related-item">
                        <span class="task-related-item__label"><i class="fa-solid fa-user-shield"></i> Старший администратор</span>
                        <strong>{{ task.creator?.name || '—' }}</strong>
                    </div>
                </div>
            </section>

            <button
                v-if="task.payment_document_name"
                @click="sendPaymentDocumentToTg"
                class="task-card__receipt-button"
                type="button"
            >
                <span class="task-card__receipt-icon"><i class="fa-brands fa-telegram"></i></span>
                <span class="task-card__receipt-copy">
                <strong>Документ чека</strong>
                <small>Отправить документ в Telegram</small>
            </span>
                <i class="fa-solid fa-arrow-up-right-from-square task-card__receipt-arrow"></i>
            </button>

            <footer class="task-card__footer">
                <span><i class="fa-regular fa-calendar-plus me-1"></i> Создано: {{ formatDate(task.created_at) }}</span>
                <span><i class="fa-solid fa-rotate me-1"></i> Обновлено: {{ formatDate(task.updated_at) }}</span>
            </footer>
        </div>

        <section v-show="activeTab === 'comments'" class="task-card__comments" aria-label="Комментарии к сделке">
            <div class="task-card__comments-heading">
                <div>
                    <h4 class="task-card__section-title"><i class="fa-regular fa-comments me-2"></i>Обсуждение сделки</h4>
                    <p class="task-card__comments-hint">Комментарии видны пользователям, имеющим доступ к этой сделке</p>
                </div>
                <button type="button" class="task-card__refresh" @click="loadComments" :disabled="commentsLoading" title="Обновить">
                    <i class="fa-solid fa-rotate" :class="{ 'fa-spin': commentsLoading }"></i>
                </button>
            </div>
            <div v-if="commentsLoading && !comments.length" class="task-card__comments-state">
                <i class="fa-solid fa-spinner fa-spin"></i> Загружаем комментарии…
            </div>
            <div v-else-if="commentsError" class="task-card__comments-state task-card__comments-state--error">
                {{ commentsError }} <button type="button" @click="loadComments">Повторить</button>
            </div>
            <div v-else-if="!comments.length" class="task-card__comments-empty">
                <span class="task-card__comments-empty-icon"><i class="fa-regular fa-message"></i></span>
                <strong>Пока нет комментариев</strong>
                <span>Оставьте первое сообщение по этой сделке</span>
            </div>
            <div v-else class="task-card__comments-list">
                <article v-for="comment in comments" :key="comment.id" class="task-comment">
                    <div class="task-comment__avatar">{{ commentAuthor(comment).slice(0, 1).toLocaleUpperCase() }}</div>
                    <div class="task-comment__content">
                        <div class="task-comment__meta">
                            <strong>{{ commentAuthor(comment) }}</strong>
                            <time :datetime="comment.created_at">{{ formatDateTime(comment.created_at) }}</time>
                        </div>
                        <p>{{ comment.comment }}</p>
                    </div>
                </article>
            </div>
            <form class="task-card__comment-form" @submit.prevent="addComment">
                <label class="task-card__comment-label" :for="commentInputId">Новый комментарий</label>
                <textarea :id="commentInputId" v-model="newComment" rows="3" maxlength="5000"
                          placeholder="Напишите комментарий к сделке…" :disabled="commentSending"></textarea>
                <div class="task-card__comment-form-footer">
                    <small>{{ newComment.length }}/5000</small>
                    <button type="submit" :disabled="commentSending || !newComment.trim()">
                        <i v-if="commentSending" class="fa-solid fa-spinner fa-spin"></i>
                        <i v-else class="fa-solid fa-paper-plane"></i>
                        {{ commentSending ? 'Отправка…' : 'Отправить' }}
                    </button>
                </div>
            </form>
        </section>
    </article>
</template>

<script>
import axios from "axios";
import { useSalesStore } from "@/stores/sales";
import { useAlertStore } from "@/stores/utillites/useAlertStore";

export default {
    name: 'TaskCard',
    props: {
        task: {
            type: Object,
            required: true
        },
        initialTab: {
            type: String,
            default: 'info'
        }
    },
    data() {
        return {
            salesStore: useSalesStore(),
            alertStore: useAlertStore(),
            activeTab: this.initialTab || 'info',
            comments: [],
            commentsLoading: false,
            commentsError: '',
            commentSending: false,
            newComment: '',
            commentInputId: `sale-comment-${this.task?.id || 'new'}`,
            statusLabels: {
                pending: 'В ожидании',
                assigned: 'Назначено',
                delivered: 'Доставлено',
                completed: 'Завершено',
                rejected: 'Отклонено'
            }
        }
    },
    watch: {
        'task.id': {
            immediate: true,
            handler() {
                this.activeTab = this.initialTab || 'info';
                this.comments = [];
                this.newComment = '';
                this.commentInputId = `sale-comment-${this.task?.id || 'new'}`;
                this.loadComments();
            }
        }
    },
    methods: {
        async loadComments() {
            if (!this.task?.id || this.commentsLoading) return;
            this.commentsLoading = true;
            this.commentsError = '';
            try {
                const response = await axios.get(`/api/sales/${this.task.id}/comments`);
                const payload = response?.data;
                this.comments = Array.isArray(payload) ? payload : (payload?.comments || payload?.data || []);
            } catch (error) {
                console.error('Не удалось загрузить комментарии сделки:', error);
                this.commentsError = error?.response?.data?.message || 'Не удалось загрузить комментарии. Попробуйте ещё раз.';
            } finally {
                this.commentsLoading = false;
            }
        },
        openCommentsTab() {
            this.activeTab = 'comments';
            if (!this.comments.length && !this.commentsLoading) this.loadComments();
        },
        async addComment() {
            const comment = this.newComment.trim();
            if (!comment || !this.task?.id || this.commentSending) return;
            this.commentSending = true;
            try {
                const response = await axios.post(`/api/sales/${this.task.id}/comments`, { comment });
                const created = response?.data?.comment || response?.data;
                if (created?.id) this.comments.push(created);
                else await this.loadComments();
                this.newComment = '';
                this.alertStore.show('Комментарий добавлен');
            } catch (error) {
                console.error('Не удалось добавить комментарий:', error);
                this.alertStore.show(error?.response?.data?.message || 'Не удалось добавить комментарий.', 'error');
            } finally {
                this.commentSending = false;
            }
        },
        commentAuthor(comment) {
            return comment?.user?.name || comment?.author?.name || comment?.user_name || 'Пользователь';
        },
        formatDateTime(date) {
            if (!date) return '—';
            const parsedDate = new Date(date);
            if (Number.isNaN(parsedDate.getTime())) return '—';
            return parsedDate.toLocaleString('ru-RU', { dateStyle: 'short', timeStyle: 'short' });
        },
        formatDate(date) {
            if (!date) return '—';
            const parsedDate = new Date(date);
            if (Number.isNaN(parsedDate.getTime())) return '—';
            return parsedDate.toLocaleDateString();
        },
        formatPrice(value) {
            if (value === null || value === undefined || value === '') return '—';
            const number = Number(value);
            return Number.isFinite(number)
                ? number.toLocaleString('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 })
                : value;
        },
        async sendPaymentDocumentToTg() {
            try {
                await this.salesStore.sendPaymentDocumentToTg(this.task.id);
                this.alertStore.show("Чек отправлен вам в телеграм-бот!");
            } catch (error) {
                console.error('Не удалось отправить чек в Telegram:', error);
                this.alertStore.show("Не удалось отправить чек в Telegram. Попробуйте ещё раз.", 'error');
            }
        },
        statusClass(status) {
            switch (status) {
                case 'pending': return 'task-status--pending';
                case 'assigned': return 'task-status--assigned';
                case 'delivered': return 'task-status--delivered';
                case 'completed': return 'task-status--completed';
                case 'rejected': return 'task-status--rejected';
                default: return 'task-status--unknown';
            }
        }
    }
}
</script>

<style scoped>
.task-card {
    --mp-blue: #2457d6;
    --mp-blue-dark: #173b91;
    --mp-red: #df3548;
    --mp-text: #17233b;
    --mp-muted: #778399;
    --mp-border: #e5eaf2;
    width: 100%;
    padding: 22px;
    color: var(--mp-text);
    background: #fff;
    border: 1px solid var(--mp-border);
    border-radius: 20px;
    box-shadow: 0 8px 28px rgba(25, 47, 89, .07);
    transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
}

.task-card:hover {
    transform: translateY(-2px);
    border-color: #d5def1;
    box-shadow: 0 14px 34px rgba(25, 47, 89, .11);
}

.task-card__header,
.task-card__title-wrap,
.task-status,
.task-detail,
.task-related-item__label,
.task-card__receipt-button,
.task-card__footer {
    display: flex;
    align-items: center;
}

.task-card__header {
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
}

.task-card__title-wrap { gap: 13px; min-width: 0; }
.task-card__icon {
    display: grid;
    place-items: center;
    flex: 0 0 46px;
    width: 46px;
    height: 46px;
    color: var(--mp-blue);
    background: #edf3ff;
    border-radius: 14px;
    font-size: 20px;
}

.task-card__eyebrow {
    margin-bottom: 3px;
    color: var(--mp-muted);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .07em;
    text-transform: uppercase;
}

.task-card__title {
    margin: 0;
    color: var(--mp-text);
    font-size: 18px;
    font-weight: 800;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.task-status {
    flex: 0 0 auto;
    gap: 7px;
    padding: 7px 11px;
    border: 1px solid transparent;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 750;
    white-space: nowrap;
}
.task-status__dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
.task-status--pending { color: #946200; background: #fff5d9; border-color: #f7e6ae; }
.task-status--assigned { color: #087e9a; background: #e3f8fc; border-color: #c3edf5; }
.task-status--delivered { color: #2457d6; background: #eaf0ff; border-color: #d5e1ff; }
.task-status--completed { color: #16804a; background: #e6f8ed; border-color: #c9efd8; }
.task-status--rejected { color: #bd293a; background: #fff0f1; border-color: #f8d4d8; }
.task-status--unknown { color: #5f6b7d; background: #f0f2f6; border-color: #e0e5ed; }

.task-card__description {
    margin: 0 0 20px;
    padding: 13px 15px;
    color: #58667d;
    background: #f7f9fc;
    border: 1px solid #edf0f6;
    border-radius: 12px;
    font-size: 14px;
    line-height: 1.6;
    white-space: pre-line;
    overflow-wrap: anywhere;
}

.task-card__section { margin-top: 20px; }
.task-card__section-title {
    display: flex;
    align-items: center;
    margin: 0 0 12px;
    color: var(--mp-text);
    font-size: 14px;
    font-weight: 800;
}
.task-card__section-title i { color: var(--mp-blue); }

.task-details-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}
.task-detail {
    gap: 11px;
    min-width: 0;
    padding: 13px;
    background: #fff;
    border: 1px solid var(--mp-border);
    border-radius: 13px;
}
.task-detail__icon {
    display: grid;
    place-items: center;
    flex: 0 0 36px;
    width: 36px;
    height: 36px;
    color: var(--mp-blue);
    background: #edf3ff;
    border-radius: 11px;
}
.task-detail--price { background: #f3f7ff; border-color: #dce6ff; }
.task-detail--price .task-detail__icon { color: #fff; background: var(--mp-blue); }
.task-detail__body { display: flex; flex-direction: column; min-width: 0; gap: 3px; }
.task-detail__label { color: var(--mp-muted); font-size: 11px; line-height: 1.35; }
.task-detail__value { color: var(--mp-text); font-size: 13px; font-weight: 750; overflow-wrap: anywhere; }
.task-detail--price .task-detail__value { color: var(--mp-blue-dark); font-size: 15px; }

.task-related-list { overflow: hidden; border: 1px solid var(--mp-border); border-radius: 13px; }
.task-related-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    padding: 11px 13px;
    font-size: 13px;
}
.task-related-item + .task-related-item { border-top: 1px solid #edf0f5; }
.task-related-item:nth-child(even) { background: #fafbfe; }
.task-related-item__label { gap: 8px; color: var(--mp-muted); flex: 0 1 48%; }
.task-related-item__label i { width: 15px; color: var(--mp-blue); text-align: center; }
.task-related-item strong { max-width: 58%; color: var(--mp-text); text-align: right; font-weight: 750; overflow-wrap: anywhere; }

.task-card__receipt-button {
    width: 100%;
    gap: 12px;
    margin-top: 18px;
    padding: 13px 15px;
    color: #087a9d;
    text-align: left;
    background: #effaff;
    border: 1px solid #cceefa;
    border-radius: 13px;
    transition: background .2s ease, border-color .2s ease;
}
.task-card__receipt-button:hover { background: #e2f7ff; border-color: #a9e4f6; }
.task-card__receipt-icon { display: grid; place-items: center; flex: 0 0 38px; width: 38px; height: 38px; color: #fff; background: #1599c4; border-radius: 12px; font-size: 19px; }
.task-card__receipt-copy { display: flex; flex: 1; flex-direction: column; gap: 3px; }
.task-card__receipt-copy strong { font-size: 13px; }
.task-card__receipt-copy small { color: #5a8191; font-size: 11px; }
.task-card__receipt-arrow { color: #1599c4; }

.task-card__footer {
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 8px 16px;
    margin-top: 20px;
    padding-top: 14px;
    color: var(--mp-muted);
    border-top: 1px solid #edf0f5;
    font-size: 11px;
}
.task-card__footer i { color: #9aa6ba; }


/* Вкладки и комментарии */
.task-card__tabs { display:flex; gap:6px; margin:0 -2px 18px; padding:5px; background:#f3f6fb; border:1px solid #e8edf5; border-radius:13px; }
.task-card__tab { display:inline-flex; align-items:center; justify-content:center; gap:8px; flex:1 1 0; min-width:0; padding:10px 12px; color:#66738a; background:transparent; border:0; border-radius:9px; font-size:13px; font-weight:750; }
.task-card__tab.is-active { color:var(--mp-blue-dark); background:#fff; box-shadow:0 2px 8px rgba(25,47,89,.08); }
.task-card__tab-count { display:inline-grid; place-items:center; min-width:21px; height:21px; padding:0 6px; color:#2457d6; background:#eaf0ff; border-radius:999px; font-size:11px; }
.task-card__comments { margin-top:4px; }
.task-card__comments-heading { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:14px; }
.task-card__comments-heading .task-card__section-title { margin-bottom:4px; }
.task-card__comments-hint { margin:0; color:var(--mp-muted); font-size:12px; }
.task-card__refresh { display:grid; place-items:center; flex:0 0 36px; width:36px; height:36px; color:var(--mp-blue); background:#edf3ff; border:0; border-radius:10px; }
.task-card__comments-list { display:flex; flex-direction:column; gap:10px; max-height:420px; overflow-y:auto; padding:2px; }
.task-comment { display:flex; align-items:flex-start; gap:11px; padding:13px; background:#fbfcff; border:1px solid var(--mp-border); border-radius:13px; }
.task-comment__avatar { display:grid; place-items:center; flex:0 0 34px; width:34px; height:34px; color:#2457d6; background:#eaf0ff; border-radius:50%; font-size:13px; font-weight:800; }
.task-comment__content { min-width:0; flex:1; }
.task-comment__meta { display:flex; flex-wrap:wrap; justify-content:space-between; gap:4px 12px; margin-bottom:6px; }
.task-comment__meta strong { color:var(--mp-text); font-size:13px; }
.task-comment__meta time { color:#929db0; font-size:11px; }
.task-comment__content p { margin:0; color:#526078; font-size:13px; line-height:1.55; white-space:pre-wrap; overflow-wrap:anywhere; }
.task-card__comments-state, .task-card__comments-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; min-height:130px; padding:22px; color:var(--mp-muted); text-align:center; background:#fafbfe; border:1px dashed #dce3ee; border-radius:13px; font-size:13px; }
.task-card__comments-state--error { color:#b72c3b; }
.task-card__comments-state button { padding:6px 12px; color:var(--mp-blue); background:#edf3ff; border:0; border-radius:8px; font-weight:700; }
.task-card__comments-empty-icon { display:grid; place-items:center; width:42px; height:42px; color:#8292ad; background:#edf1f7; border-radius:50%; font-size:18px; }
.task-card__comments-empty strong { color:var(--mp-text); font-size:13px; }
.task-card__comments-empty span:last-child { font-size:12px; }
.task-card__comment-form { margin-top:16px; padding:14px; background:#f7f9fd; border:1px solid var(--mp-border); border-radius:13px; }
.task-card__comment-label { display:block; margin-bottom:8px; color:var(--mp-text); font-size:12px; font-weight:800; }
.task-card__comment-form textarea { display:block; width:100%; min-height:82px; padding:11px 12px; color:var(--mp-text); background:#fff; border:1px solid #dce3ee; border-radius:10px; outline:none; resize:vertical; font:inherit; font-size:13px; line-height:1.5; }
.task-card__comment-form textarea:focus { border-color:#8daaf0; box-shadow:0 0 0 3px rgba(36,87,214,.09); }
.task-card__comment-form-footer { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:10px; }
.task-card__comment-form-footer small { color:#929db0; font-size:11px; }
.task-card__comment-form-footer button { display:inline-flex; align-items:center; gap:8px; padding:9px 14px; color:#fff; background:var(--mp-blue); border:0; border-radius:9px; font-size:12px; font-weight:750; }
.task-card__comment-form-footer button:disabled { opacity:.5; cursor:not-allowed; }
@media (max-width:575.98px) { .task-card__tab { padding:9px 6px; gap:5px; font-size:11px; } .task-card__comments-hint { max-width:250px; } }

@media (max-width: 575.98px) {
    .task-card { padding: 16px; border-radius: 16px; }
    .task-card__header { align-items: flex-start; flex-direction: column; gap: 12px; }
    .task-card__title { font-size: 16px; }
    .task-card__icon { flex-basis: 40px; width: 40px; height: 40px; font-size: 18px; }
    .task-details-grid { grid-template-columns: minmax(0, 1fr); }
    .task-detail { padding: 11px; }
    .task-related-item { flex-direction: column; gap: 5px; }
    .task-related-item strong { max-width: 100%; text-align: left; padding-left: 23px; }
    .task-card__footer { flex-direction: column; align-items: flex-start; }
}

@media (prefers-reduced-motion: reduce) {
    .task-card, .task-card__receipt-button { transition: none; }
}
</style>
