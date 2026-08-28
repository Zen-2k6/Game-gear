<?php require APP_PATH . '/Views/partials/user_header.php'; ?>
<section class="contact-hero"><div><span class="contact-status"><i></i> SUPPORT TEAM ONLINE</span><p class="eyebrow">CONTACT GAMEGEAR HUB</p><h1>How can we <em>help?</em></h1><p>Questions about a product, delivery, payment, or an existing order? Reach out and our team will help you find the next step.</p></div><div class="contact-hero-lines" aria-hidden="true"><span></span><span></span><span></span></div></section>

<section class="contact-channel-grid" aria-label="Contact options">
    <a href="mailto:support@gamegear.test"><span>✉</span><small>EMAIL SUPPORT</small><strong>support@gamegear.test</strong><p>For products, orders, and general help.</p></a>
    <a href="tel:+959111111111"><span>☎</span><small>CALL US</small><strong>+95 9 111 111 111</strong><p>Monday–Saturday, 9:00 AM–6:00 PM.</p></a>
    <article><span>⌖</span><small>VISIT US</small><strong>Yangon, Myanmar</strong><p>Contact us before visiting for assistance.</p></article>
</section>

<section class="contact-layout">
    <article class="contact-form-panel"><div><p class="eyebrow">SEND A MESSAGE</p><h2>Tell us what you need.</h2><p>This form opens your email application with the message details ready to send.</p></div>
        <form action="mailto:support@gamegear.test" method="post" enctype="text/plain">
            <div class="two"><label>Full name<input name="Name" required maxlength="100" value="<?= e(currentUser()['name'] ?? '') ?>" placeholder="Your name"></label><label>Email address<input type="email" name="Email" required maxlength="190" value="<?= e(currentUser()['email'] ?? '') ?>" placeholder="you@example.com"></label></div>
            <label>What can we help with?<select name="Topic" required><option value="">Select a topic</option><option>Product information</option><option>Order status</option><option>Delivery question</option><option>Payment support</option><option>Returns and exchanges</option><option>Other</option></select></label>
            <label>Order number <small>(optional)</small><input name="Order number" maxlength="30" placeholder="e.g. #1024"></label>
            <label>Message<textarea name="Message" required rows="6" maxlength="2000" placeholder="Describe your question or issue..."></textarea></label>
            <button type="submit">Open Email & Send →</button>
        </form>
    </article>
    <aside class="contact-sidebar">
        <article><p class="eyebrow">BUSINESS HOURS</p><h3>When we’re available</h3><div class="hours-list"><div><span>Monday–Friday</span><strong>9:00 AM–6:00 PM</strong></div><div><span>Saturday</span><strong>10:00 AM–4:00 PM</strong></div><div><span>Sunday</span><strong>Closed</strong></div></div></article>
        <article><p class="eyebrow">QUICK HELP</p><h3>Before you contact us</h3><a href="<?= url('products') ?>">Browse products and specifications →</a><a href="<?= url('orders') ?>">Check your order history →</a><a href="<?= url('cart') ?>">Review your shopping cart →</a><a href="<?= url('about', [], 'what-we-offer') ?>">See what we offer →</a></article>
        <article><p class="eyebrow">FOLLOW GAMEGEAR</p><div class="contact-socials"><a href="https://www.facebook.com/" target="_blank" rel="noopener">Facebook ↗</a><a href="https://www.instagram.com/" target="_blank" rel="noopener">Instagram ↗</a><a href="https://www.youtube.com/" target="_blank" rel="noopener">YouTube ↗</a></div></article>
    </aside>
</section>

<section class="contact-faq"><div class="section-heading"><div><p class="eyebrow">COMMON QUESTIONS</p><h2>Quick answers.</h2></div></div><div class="contact-faq-grid"><article><h3>How quickly are orders dispatched?</h3><p>Our target is to prepare confirmed orders within 24 hours, excluding Sundays and public holidays.</p></article><article><h3>What payment methods are available?</h3><p>The academic checkout supports demo card, KBZPay, and WavePay payment flows.</p></article><article><h3>Can I change an order?</h3><p>Contact us as soon as possible with your order number. Changes depend on the current fulfilment status.</p></article><article><h3>How do returns work?</h3><p>Eligible products can be returned within seven days. Contact support before sending any product back.</p></article></div></section>
<?php require APP_PATH . '/Views/partials/user_footer.php'; ?>
