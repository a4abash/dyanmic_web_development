<?php include 'includes/header.php'; ?>
<title>Privacy Policy – Ripper Tech &amp; Solutions</title>

<style>
:root {
    --navy: #1f2a3a;
    --indigo: #667eea;
    --purple: #764ba2;
    --text: #2c3e50;
    --muted: #6c757d;
    --surface: #ffffff;
    --surface-2: #f7f9fc;
    --success: #2e7d32;
    --danger: #c62828;
}

.privacy-hero {
    background: linear-gradient(135deg, var(--indigo), var(--purple));
    color: #fff;
    padding: 48px 20px;
}

.privacy-hero .wrap {
    max-width: 980px;
    margin: 0 auto;
}

.privacy-hero h1 {
    margin: 0 0 8px;
    font-weight: 800;
    letter-spacing: .2px;
}

.privacy-hero .meta {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    font-size: .95rem;
    opacity: .95;
}

.privacy-hero .badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: rgba(255, 255, 255, .12);
    border: 1px solid rgba(255, 255, 255, .25);
    padding: .4rem .75rem;
    border-radius: 999px;
}

.privacy-body {
    padding: 32px 20px 56px;
    background: var(--surface-2);
}

.privacy-grid {
    max-width: 980px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 24px;
}

@media (max-width: 992px) {
    .privacy-grid {
        grid-template-columns: 1fr;
    }
}

/* TOC */
.privacy-toc {
    position: sticky;
    top: 90px;
    align-self: start;
    background: var(--surface);
    border-radius: 14px;
    padding: 16px 14px;
    box-shadow: 0 10px 30px rgba(31, 42, 58, .08);
    border: 1px solid #e9eef5;
}

.privacy-toc h3 {
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--muted);
    margin: 0 0 10px;
}

.privacy-toc a {
    display: block;
    padding: 10px 10px;
    border-radius: 10px;
    color: var(--text);
    text-decoration: none;
}

.privacy-toc a:hover {
    background: #f0f3fa;
}

.privacy-toc a .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--indigo);
    display: inline-block;
    margin-right: .5rem;
}

/* Cards / Sections */
.privacy-card {
    background: var(--surface);
    border-radius: 16px;
    padding: 22px 22px;
    box-shadow: 0 10px 30px rgba(31, 42, 58, .08);
    border: 1px solid #e9eef5;
    margin-bottom: 18px;
}

.privacy-card h2 {
    font-size: 1.35rem;
    margin: 0 0 .75rem;
    display: flex;
    align-items: center;
    gap: .6rem;
}

.privacy-card h2 .ico {
    width: 26px;
    height: 26px;
    display: inline-grid;
    place-items: center;
    border-radius: 8px;
    color: #fff;
    background: linear-gradient(135deg, var(--indigo), var(--purple));
}

.privacy-card p {
    margin: .35rem 0 .75rem;
    color: var(--text);
}

.privacy-card ul {
    margin: .2rem 0 .75rem 1.2rem;
}

.privacy-note {
    background: #fffaf0;
    border: 1px solid #ffe4a3;
    color: #8a6d1a;
    padding: 12px 14px;
    border-radius: 10px;
    margin: 8px 0 0;
}

/* Callout blocks */
.callout {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    padding: 14px;
    border-radius: 14px;
    border: 1px solid #e8eef6;
    background: #f9fbfe;
    margin-top: 8px;
}

.callout .k {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    color: #fff;
    background: var(--indigo);
}

.callout.success {
    border-color: #dcedd9;
    background: #f4fbf3;
}

.callout.success .k {
    background: var(--success);
}

.callout.danger {
    border-color: #f6d9d9;
    background: #fff7f7;
}

.callout.danger .k {
    background: var(--danger);
}

.privacy-footer-cta {
    max-width: 980px;
    margin: 18px auto 0;
    background: linear-gradient(135deg, var(--indigo), var(--purple));
    color: #fff;
    border-radius: 18px;
    padding: 18px 18px;
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: center;
}

.privacy-footer-cta a {
    background: #fff;
    color: var(--navy);
    text-decoration: none;
    padding: 10px 14px;
    border-radius: 999px;
    font-weight: 600;
}
</style>

<section class="privacy-hero" role="banner" aria-label="Privacy Policy hero">
    <div class="wrap">
        <h1>Privacy Policy</h1>
        <div class="meta" aria-label="Meta information">
            <span class="badge"><i class="fas fa-calendar-alt" aria-hidden="true"></i> Last updated:
                <?php echo date('F j, Y'); ?></span>
            <span class="badge"><i class="fas fa-shield-alt" aria-hidden="true"></i> GDPR aligned</span>
            <span class="badge"><i class="fas fa-user-check" aria-hidden="true"></i> Data Minimisation</span>
        </div>
    </div>
</section>

<section class="privacy-body">
    <div class="privacy-grid">
        <!-- Table of contents -->
        <nav class="privacy-toc" aria-label="On-page navigation">
            <h3>On this page</h3>
            <a href="#intro"><span class="dot"></span>Introduction</a>
            <a href="#data-we-collect"><span class="dot"></span>Data We Collect</a>
            <a href="#why-we-collect"><span class="dot"></span>Why We Collect Data</a>
            <a href="#legal-basis"><span class="dot"></span>Legal Basis (GDPR)</a>
            <a href="#your-rights"><span class="dot"></span>Your Rights</a>
            <a href="#retention"><span class="dot"></span>Data Retention</a>
            <a href="#cookies"><span class="dot"></span>Cookies &amp; Sessions</a>
            <a href="#ethics"><span class="dot"></span>Ethical Commitment</a>
            <a href="#contact"><span class="dot"></span>Contact</a>
        </nav>

        <!-- Content -->
        <div>
            <article id="intro" class="privacy-card" aria-labelledby="intro-title">
                <h2 id="intro-title"><span class="ico"><i class="fas fa-info" aria-hidden="true"></i></span>
                    Introduction</h2>
                <p>
                    At <strong>Ripper Tech &amp; Solutions</strong>, we respect your privacy and are committed to
                    protecting your personal data.
                    This Policy explains how we collect, use, and safeguard your information when you interact with our
                    website and services.
                </p>
                <div class="callout">
                    <div class="k"><i class="fas fa-gavel" aria-hidden="true"></i></div>
                    <div>
                        The General Data Protection Regulation (GDPR) was <strong>adopted on 14 April 2016</strong> and
                        <strong>enforced on 25 May 2018</strong>. This policy reflects those requirements.
                    </div>
                </div>
            </article>

            <article id="data-we-collect" class="privacy-card" aria-labelledby="collect-title">
                <h2 id="collect-title"><span class="ico"><i class="fas fa-database" aria-hidden="true"></i></span> Data
                    We Collect</h2>
                <ul>
                    <li><strong>Account Data:</strong> name, email address, username, role.</li>
                    <li><strong>Contact Form Data:</strong> name, email address, and message.</li>
                    <li><strong>Session Data:</strong> cookies required for login and session management.</li>
                </ul>
                <p class="privacy-note">We do not sell your personal data or share it with third parties for marketing.
                </p>
            </article>

            <article id="why-we-collect" class="privacy-card" aria-labelledby="why-title">
                <h2 id="why-title"><span class="ico"><i class="fas fa-bullseye" aria-hidden="true"></i></span> Why We
                    Collect Data</h2>
                <ul>
                    <li>Provide and manage your account</li>
                    <li>Respond to your enquiries</li>
                    <li>Secure our system (fraud prevention, access control)</li>
                </ul>
            </article>

            <article id="legal-basis" class="privacy-card" aria-labelledby="legal-title">
                <h2 id="legal-title"><span class="ico"><i class="fas fa-scale-balanced" aria-hidden="true"></i></span>
                    Legal Basis (GDPR)</h2>
                <p>We process personal data under the following legal bases:</p>
                <ul>
                    <li><strong>Article 6(1)(b):</strong> necessary to perform a contract (providing services).</li>
                    <li><strong>Article 6(1)(f):</strong> our legitimate interests (security, troubleshooting).</li>
                    <li><strong>Consent:</strong> contact forms require explicit consent.</li>
                </ul>
            </article>

            <article id="your-rights" class="privacy-card" aria-labelledby="rights-title">
                <h2 id="rights-title"><span class="ico"><i class="fas fa-user-shield" aria-hidden="true"></i></span>
                    Your Rights</h2>
                <p>Under GDPR, you can:</p>
                <ul>
                    <li>Access the data we hold about you</li>
                    <li>Request correction or deletion</li>
                    <li>Restrict or object to processing</li>
                    <li>Request a copy of your data (portability)</li>
                </ul>
                <div class="callout success">
                    <div class="k"><i class="fas fa-envelope-open-text" aria-hidden="true"></i></div>
                    <div>Email <a href="mailto:a4abash@gmail.com">a4abash@gmail.com</a> to exercise any of your rights.
                    </div>
                </div>
            </article>

            <article id="retention" class="privacy-card" aria-labelledby="ret-title">
                <h2 id="ret-title"><span class="ico"><i class="fas fa-hourglass-half" aria-hidden="true"></i></span>
                    Data Retention</h2>
                <ul>
                    <li>User accounts are retained while active.</li>
                    <li>Contact form messages are stored for up to <strong>180 days</strong>.</li>
                    <li>Session cookies expire on logout or after 30 minutes of inactivity.</li>
                </ul>
            </article>

            <article id="cookies" class="privacy-card" aria-labelledby="cookie-title">
                <h2 id="cookie-title"><span class="ico"><i class="fas fa-cookie-bite" aria-hidden="true"></i></span>
                    Cookies &amp; Sessions</h2>
                <p>We use only <strong>essential</strong> cookies for authentication and session management. No
                    analytics or marketing cookies are set.</p>
                <div class="callout">
                    <div class="k"><i class="fas fa-lock" aria-hidden="true"></i></div>
                    <div>Passwords are hashed using industry-standard algorithms; admin features are role-restricted.
                    </div>
                </div>
            </article>

            <article id="ethics" class="privacy-card" aria-labelledby="ethics-title">
                <h2 id="ethics-title"><span class="ico"><i class="fas fa-heart" aria-hidden="true"></i></span> Ethical
                    Commitment</h2>
                <p>We follow the principles of data minimisation, least privilege (roles), and transparency. We do not
                    use dark patterns or sell personal data.</p>
                <div class="callout danger">
                    <div class="k"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i></div>
                    <div>If you believe your data has been misused, contact us immediately. You can also lodge a
                        complaint with your local data protection authority.</div>
                </div>
            </article>

            <article id="contact" class="privacy-card" aria-labelledby="contact-title">
                <h2 id="contact-title"><span class="ico"><i class="fas fa-paper-plane" aria-hidden="true"></i></span>
                    Contact</h2>
                <p>Questions about this policy or your data?</p>
                <ul>
                    <li>Email: <a href="mailto:a4abash@gmail.com">a4abash@gmail.com</a></li>
                </ul>
            </article>

            <div class="privacy-footer-cta" role="region" aria-label="Helpful links">
                <div>
                    <strong>Need to make a data request?</strong><br>
                    We’ll respond as soon as possible.
                </div>
                <div>
                    <a href="contact.php" class="btn">Contact Support</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>