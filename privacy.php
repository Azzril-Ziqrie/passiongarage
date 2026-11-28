<?php
session_start();
require __DIR__ . '/includes/site_gate.php';

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/db_schema.php';
require_once __DIR__ . '/includes/currency_helper.php';
require_once __DIR__ . '/includes/layout.php';

siteHeadOpen([
    'title'       => 'Privacy Policy',
    'description' => 'How ' . BRAND_NAME . ' collects, uses and protects your personal information.',
    'css'         => ['assets/css/pages.css'],
]);
siteBodyOpen(['bodyClass' => 'page-privacy', 'searchAction' => 'shop.php']);
?>
  <main class="page-main">
    <article class="doc-body">
      <header class="doc-head reveal">
        <p class="doc-eyebrow">Privacy</p>
        <h1 class="doc-title">Privacy</h1>
      </header>
      <p class="doc-text reveal"><?= esc(BRAND_NAME) ?> respects your privacy. This policy explains what personal information we collect on this website and the other sites we run, how we use it, and the laws we follow when we handle it.</p>
      <p class="doc-text reveal">This policy took effect on 28 January 2022. We last updated it on 28 November 2026.</p>

      <section class="doc-sec reveal">
        <h2>Information We Collect</h2>
        <p>We collect two kinds of information: what you give us when you use our services or join a promotion, and what your device sends us when you visit.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>Log Data</h2>
        <p>Our servers may record the standard data your browser sends when you visit. That can include your IP address, browser type and version, the pages you open, the date and time of your visit, how long you stay on each page, and technical details about any errors you hit.</p>
        <p>This data may not identify you on its own. Someone could still combine it with other data to identify you.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>Personal Information</h2>
        <p>We may ask you for:</p>
        <ul>
          <li>Name</li>
          <li>Email</li>
          <li>Phone or mobile number</li>
          <li>Home or mailing address</li>
        </ul>
      </section>
      <section class="doc-sec reveal">
        <h2>Why We Process Your Personal Information</h2>
        <p>We collect and use your personal information only when we have a legitimate reason. When we do, we collect only what we need to serve you.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>Collection and Use of Information</h2>
        <p>We may collect personal information when you:</p>
        <ul>
          <li>Sign up for updates by email or on social media</li>
          <li>Open our content in a browser or on a mobile device</li>
          <li>Contact us by email, social media or a similar channel</li>
          <li>Mention us on social media</li>
        </ul>
        <p>We may collect, hold, use and disclose your information for the purposes below. We won’t use it in a way that conflicts with them.</p>
        <ul>
          <li>To let you customise your experience on our website</li>
          <li>To contact and talk with you</li>
          <li>For internal records and administration</li>
          <li>To meet our legal obligations and resolve disputes</li>
        </ul>
        <p>We may combine what we collect about you with general information or research data from other trusted sources.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>Security of Your Personal Information</h2>
        <p>While we hold your personal information, we protect it with commercially acceptable means against loss, theft, and unauthorised access, disclosure, copying, use or change.</p>
        <p>We do our best, but no method of sending or storing data online is 100% secure, and nobody can promise complete security. If a data breach happens, we will follow the laws that apply to us.</p>
        <p>You choose your password and keep it strong and private.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>How Long We Keep Your Personal Information</h2>
        <p>We keep your personal information only as long as we need it for the purposes in this policy. Once we no longer need it, we delete it or remove every detail that identifies you.</p>
        <p>We may keep some information longer to meet a legal, accounting or reporting obligation, or for archiving in the public interest, research or statistics.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>Children’s Privacy</h2>
        <p>We don’t aim our products or services at children under 13, and we don’t knowingly collect personal information about them.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>Disclosure of Personal Information to Third Parties</h2>
        <p>We may share personal information with:</p>
        <ul>
          <li>A parent company, subsidiary or affiliate</li>
          <li>Service providers who need it to do their work for us, such as IT, data storage, hosting, advertising and analytics providers</li>
          <li>Our employees, contractors and related entities</li>
          <li>Our current or potential agents and business partners</li>
          <li>Sponsors or promoters of a competition or promotion we run</li>
          <li>Courts, tribunals, regulators and law enforcement, when the law requires it, in connection with legal proceedings, or to establish, use or defend our legal rights</li>
          <li>Third parties, including agents and subcontractors, who help us provide information, products, services or direct marketing to you, or who collect and process data for us</li>
        </ul>
      </section>
      <section class="doc-sec reveal">
        <h2>International Transfers of Personal Information</h2>
        <p>We and our partners, affiliates and service providers store and process personal information wherever we keep facilities. Those places may have different data protection laws from your country. If we transfer your information to a third party in another country, we will (i) follow the applicable law for that transfer and (ii) protect the information as this policy describes.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>Your Rights</h2>
        <p>You can choose not to give us personal information. Some parts of the website may then work differently for you. We won’t treat you differently for using your rights. If you do give us personal information, we will collect, hold, use and disclose it as this policy describes. You can ask us for the details of any personal information we hold about you.</p>
        <p>If a third party gives us personal information about you, we protect it under this policy. If you give us personal information about someone else, you confirm that you have their consent.</p>
        <p>If you agreed to receive direct marketing from us, you can change your mind at any time. You can unsubscribe from our emails or opt out of our messages. We may ask you for some details to confirm your identity first.</p>
        <p>If you think any information we hold about you is wrong, out of date, incomplete, irrelevant or misleading, contact us. We will take reasonable steps to correct it.</p>
        <p>If you think we have broken a data protection law and want to complain, contact us with the full details. We will look into your complaint, reply in writing with what we found, and tell you what we will do about it. You can also take your complaint to a regulator or data protection authority.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>Use of Cookies</h2>
        <p>We use cookies to learn how you use our site. A cookie is a small piece of data our website saves on your device and reads on each visit. Cookies let us show you content that matches the preferences you set.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>Limits of Our Policy</h2>
        <p>Our website may link to sites we don’t run. We don’t control their content or policies, and we can’t take responsibility for how they handle your privacy.</p>
      </section>
      <section class="doc-sec reveal">
        <h2>Changes to This Policy</h2>
        <p>We may change this policy to reflect changes in how we work, in accepted practice, or in the law. When we do, we will post the new version on this page.</p>
        <p>Where the law requires it, we will ask your permission, or let you opt in or out, before we use your personal information in a new way.</p>
      </section>
    </article>
  </main>
<?php
siteBodyClose();
siteDocClose();
