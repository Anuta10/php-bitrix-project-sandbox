<div class="birthday-modal birthday-compose" data-congratulate-modal hidden>
    <div class="birthday-modal-backdrop" data-congratulate-close></div>

    <section class="birthday-modal-card birthday-compose-card" role="dialog" aria-modal="true" aria-labelledby="birthday-modal-title">
        <button class="birthday-modal-close" type="button" data-congratulate-close>×</button>
        <h2 id="birthday-modal-title"><?= $locale === 'ru' ? 'Поздравить сотрудника' : 'Congratulate employee' ?></h2>
        <p class="birthday-compose-person" data-congratulate-person></p>

        <section class="birthday-compose-section">
            <h3><?= $locale === 'ru' ? 'Открытки' : 'Cards' ?></h3>
            <div class="birthday-compose-carousel" data-compose-carousel="cards">
                <button class="birthday-compose-arrow" type="button" data-compose-prev aria-label="Previous">‹</button>
                <div class="birthday-compose-options">
                    <label>
                        <input type="radio" name="demo-card" value="balloons" checked>
                        <span class="birthday-art-card art-balloons">🎈<small>Happy Birthday</small></span>
                    </label>
                    <label>
                        <input type="radio" name="demo-card" value="heart">
                        <span class="birthday-art-card art-heart">💝<small>For you</small></span>
                    </label>
                    <label>
                        <input type="radio" name="demo-card" value="cake">
                        <span class="birthday-art-card art-cake">🎂<small>Best wishes</small></span>
                    </label>
                    <label>
                        <input type="radio" name="demo-card" value="sparkles">
                        <span class="birthday-art-card art-sparkles">✨<small>Have a great day</small></span>
                    </label>
                </div>
                <button class="birthday-compose-arrow" type="button" data-compose-next aria-label="Next">›</button>
            </div>
        </section>

        <section class="birthday-compose-section">
            <h3><?= $locale === 'ru' ? 'Стикеры' : 'Stickers' ?></h3>
            <div class="birthday-compose-carousel" data-compose-carousel="stickers">
                <button class="birthday-compose-arrow" type="button" data-compose-prev aria-label="Previous">‹</button>
                <div class="birthday-compose-options birthday-sticker-options">
                    <label><input type="radio" name="demo-sticker" value="party" checked><span class="birthday-sticker">🥳</span></label>
                    <label><input type="radio" name="demo-sticker" value="flower"><span class="birthday-sticker">🌷</span></label>
                    <label><input type="radio" name="demo-sticker" value="gift"><span class="birthday-sticker">🎁</span></label>
                    <label><input type="radio" name="demo-sticker" value="star"><span class="birthday-sticker">🌟</span></label>
                    <label><input type="radio" name="demo-sticker" value="confetti"><span class="birthday-sticker">🎊</span></label>
                </div>
                <button class="birthday-compose-arrow" type="button" data-compose-next aria-label="Next">›</button>
            </div>
        </section>

        <label class="birthday-message-field">
            <span><?= $locale === 'ru' ? 'Текст поздравления' : 'Message' ?></span>
            <textarea
                rows="6"
                maxlength="1000"
                data-birthday-message
                placeholder="<?= $locale === 'ru' ? 'Введите текст поздравления' : 'Write a congratulation message' ?>"
            ></textarea>
            <small><span data-birthday-message-count>0</span> / 1000</small>
        </label>

        <div class="birthday-modal-actions birthday-compose-actions">
            <button class="birthday-congratulate" type="button" data-congratulate-confirm>
                <?= $locale === 'ru' ? 'Отправить' : 'Send' ?>
            </button>
        </div>
    </section>
</div>
