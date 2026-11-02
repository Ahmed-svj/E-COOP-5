/* Landing page for E-COOP - structure follows the reference: nav, hero, features, how it works, roles, CTA, footer */
var LANDING = {
  en: {
    navFeatures: 'Features', navHow: 'How It Works', navRoles: 'Roles', openApp: 'Open App',
    heroEyebrow: 'Jubail Industrial College · Cooperative Training',
    heroTitle: 'Cooperative training, from application to final evaluation',
    heroLead: 'E-COOP brings students, host companies, academic supervisors and the COOP office onto one system. Apply for placements, log attendance with a daily code, submit reports and track evaluations, in Arabic or English.',
    getStarted: 'Get Started', seeHow: 'See How It Works',
    factRoles: 'roles, one system', factLangs: 'languages, full RTL', factCode: 'digit daily code, no camera needed',
    featuresTitle: 'Everything a COOP term needs',
    featuresLead: 'Each feature maps to a requirement in the COOP analysis, so what the college asked for is what the system does.',
    f1t: 'Opportunity board', f1d: 'Companies post placements, the COOP office approves them, and students filter by major and city.',
    f2t: 'Applications', f2d: 'Students apply once per opportunity and follow each application from Submitted to Accepted.',
    f3t: 'Daily code attendance', f3d: 'Companies generate a code each morning. Students enter it on any phone, and it expires at midnight.',
    f4t: 'Final reports', f4d: 'Students upload one PDF or DOCX report. Supervisors approve it or request a revision.',
    f5t: 'Evaluations', f5d: 'Companies and supervisors score each trainee from 0 to 100, with comments the student can read.',
    f6t: 'Analytics and audit', f6d: 'The COOP office sees placement and attendance rates, and every admin action is logged.',
    howTitle: 'How a COOP term runs',
    s1t: 'Company joins', s1d: 'A company registers and the COOP office approves it before it can post.',
    s2t: 'Opportunity posted', s2d: 'The company posts a placement. It stays hidden until an admin approves it.',
    s3t: 'Student placed', s3d: 'Students apply, the company accepts, and the student joins the placement with an assigned supervisor.',
    s4t: 'Training and evaluation', s4d: 'Daily check-ins, supervisor approvals, the final report and both evaluations close the term.',
    rolesTitle: 'Built for every side of the placement',
    rStudent: 'Students', rStudentL: ['Browse and filter approved opportunities', 'Apply and track application status', 'Check in with the daily code', 'Submit the final training report'],
    rCompany: 'Companies', rCompanyL: ['Post opportunities for admin approval', 'Accept or reject applicants', 'Generate the daily attendance code', 'Evaluate each trainee'],
    rSupervisor: 'Academic supervisors', rSupervisorL: ['Monitor assigned students', 'Approve or reject attendance', 'Review final reports', 'Submit academic evaluations'],
    rAdmin: 'COOP office', rAdminL: ['Approve companies and opportunities', 'Manage and suspend accounts', 'Assign academic supervisors', 'View analytics and the audit log'],
    ctaTitle: 'Ready to start your COOP term?', ctaLead: 'Open the demo and try it from any of the four roles.', ctaBtn: 'Open E-COOP',
    footNote: 'Jubail Industrial College · Department of Business Administration',
    footDemo: 'Demo with sample records. No real student data.',
    backHome: 'Back'
  },
  ar: {
    navFeatures: 'المزايا', navHow: 'آلية العمل', navRoles: 'الأدوار', openApp: 'فتح التطبيق',
    heroEyebrow: 'كلية الجبيل الصناعية · التدريب التعاوني',
    heroTitle: 'التدريب التعاوني من التقديم حتى التقييم النهائي',
    heroLead: 'يجمع E-COOP الطلاب وجهات التدريب والمشرفين الأكاديميين ومكتب التدريب التعاوني في نظام واحد. قدّم على الفرص، وسجّل حضورك برمز يومي، وارفع التقارير، وتابع التقييمات بالعربية أو الإنجليزية.',
    getStarted: 'ابدأ الآن', seeHow: 'تعرّف على آلية العمل',
    factRoles: 'أدوار في نظام واحد', factLangs: 'لغتان مع دعم كامل للاتجاه من اليمين', factCode: 'أرقام لرمز الحضور بدون كاميرا',
    featuresTitle: 'كل ما يحتاجه فصل التدريب التعاوني',
    featuresLead: 'كل ميزة مرتبطة بمتطلب في تحليل النظام، فما طلبته الكلية هو ما يقدمه النظام.',
    f1t: 'لوحة الفرص', f1d: 'تنشر الجهات فرص التدريب، ويعتمدها مكتب التدريب، ويصفّيها الطلاب حسب التخصص والمدينة.',
    f2t: 'الطلبات', f2d: 'يقدّم الطالب مرة واحدة لكل فرصة ويتابع طلبه من الإرسال حتى القبول.',
    f3t: 'الحضور برمز يومي', f3d: 'تُنشئ الجهة رمزًا كل صباح، ويُدخله الطالب من أي جوال، وينتهي عند منتصف الليل.',
    f4t: 'التقارير النهائية', f4d: 'يرفع الطالب تقريرًا واحدًا بصيغة PDF أو DOCX، ويعتمده المشرف أو يطلب تعديله.',
    f5t: 'التقييمات', f5d: 'تقيّم الجهة والمشرف كل متدرب من 0 إلى 100 مع ملاحظات يطّلع عليها الطالب.',
    f6t: 'التحليلات والتدقيق', f6d: 'يطّلع مكتب التدريب على نسب التسكين والحضور، وتُسجَّل كل إجراءات المسؤول.',
    howTitle: 'كيف يسير فصل التدريب',
    s1t: 'انضمام الجهة', s1d: 'تسجّل الجهة ويعتمدها مكتب التدريب قبل أن تتمكن من النشر.',
    s2t: 'نشر الفرصة', s2d: 'تنشر الجهة فرصة تدريب تبقى مخفية حتى يعتمدها المسؤول.',
    s3t: 'تسكين الطالب', s3d: 'يقدّم الطلاب، وتقبلهم الجهة، وينضم الطالب إلى التدريب مع مشرف أكاديمي.',
    s4t: 'التدريب والتقييم', s4d: 'الحضور اليومي واعتماد المشرف والتقرير النهائي والتقييمان تختم الفصل.',
    rolesTitle: 'مصمم لكل أطراف التدريب',
    rStudent: 'الطلاب', rStudentL: ['تصفّح الفرص المعتمدة وتصفيتها', 'التقديم ومتابعة حالة الطلب', 'تسجيل الحضور بالرمز اليومي', 'رفع التقرير النهائي'],
    rCompany: 'جهات التدريب', rCompanyL: ['نشر الفرص لاعتمادها', 'قبول المتقدمين أو رفضهم', 'إنشاء رمز الحضور اليومي', 'تقييم كل متدرب'],
    rSupervisor: 'المشرفون الأكاديميون', rSupervisorL: ['متابعة الطلاب المعيّنين', 'اعتماد الحضور أو رفضه', 'مراجعة التقارير النهائية', 'رفع التقييمات الأكاديمية'],
    rAdmin: 'مكتب التدريب التعاوني', rAdminL: ['اعتماد الجهات والفرص', 'إدارة الحسابات وإيقافها', 'تعيين المشرفين الأكاديميين', 'الاطلاع على التحليلات وسجل التدقيق'],
    ctaTitle: 'جاهز لبدء فصل التدريب التعاوني؟', ctaLead: 'افتح النسخة التجريبية وجرّبها من أي دور من الأدوار الأربعة.', ctaBtn: 'فتح E-COOP',
    footNote: 'كلية الجبيل الصناعية · قسم إدارة الأعمال',
    footDemo: 'نسخة تجريبية ببيانات نموذجية، لا تتضمن بيانات طلاب حقيقية.',
    backHome: 'رجوع'
  }
};

/* outline icons, 24 grid, 1.5 stroke */
var LICONS = {
  board: 'M4 5h16v14H4zM4 9h16M9 9v10',
  apps: 'M7 3h7l4 4v14H7zM14 3v4h4M10 12h5M10 16h5',
  code: 'M5 4h14v16H5zM8.5 9v6M12 9v6M15.5 9v6',
  report: 'M6 3h9l3 3v15H6zM9 11h6M9 15h6M9 7h3',
  eval: 'M12 3l2.6 5.6 6 .7-4.5 4.1 1.2 6-5.3-3-5.3 3 1.2-6L3.4 9.3l6-.7z',
  chart: 'M4 20h16M7 16v-5M12 16V7M17 16v-8'
};

/* home page in the reference layout: navy hero with a sign-in card, light sections, charcoal footer */
Object.assign(LANDING.en, {
  hEyebrow: 'GET STARTED', hTitle: 'See how cooperative training works.',
  hLead: 'Sign in with your email to follow your placement, attendance, reports and evaluations.',
  officePhone: '013 340 9000', officeEmail: 'coop.office@jic.edu.sa',
  fEmail: 'Email', fEmailPh: 'Enter your email', fPass: 'Password', fPassPh: 'Enter your password', fSubmit: 'Sign In',
  fTerms: 'By signing in, you agree to the Jubail Industrial College cooperative training policy and the PDPL personal data terms.',
  fDemo: 'Use a demo account instead', fDemoHint: 'Demo: any password works for the sample accounts, for example ahmed.almousa@jic.edu.sa.',
  eEmail: 'Enter your email, for example name@jic.edu.sa.', eNoUser: 'No E-COOP account uses this email. Check it or use a demo account.', ePass: 'Enter your password.', eSuspended: 'This account is suspended. Contact the COOP office to reactivate it.',
  footBuilt: 'Built by Business Administration students at Jubail Industrial College.', footFor: 'Cooperative Training Management System.',
  footPlatform: 'Platform', footAccounts: 'Accounts', footRights: '\u00a9 2026 E-COOP · Jubail Industrial College. All rights reserved.',
  accStudent: 'Students', accCompany: 'Companies', accSupervisor: 'Supervisors', accAdmin: 'COOP office', langSwitch: 'العربية'
});
Object.assign(LANDING.ar, {
  hEyebrow: 'ابدأ الآن', hTitle: 'تعرّف على سير التدريب التعاوني.',
  hLead: 'سجّل الدخول ببريدك الإلكتروني لمتابعة تدريبك وحضورك وتقاريرك وتقييماتك.',
  officePhone: '013 340 9000', officeEmail: 'coop.office@jic.edu.sa',
  fEmail: 'البريد الإلكتروني', fEmailPh: 'أدخل بريدك الإلكتروني', fPass: 'كلمة المرور', fPassPh: 'أدخل كلمة المرور', fSubmit: 'تسجيل الدخول',
  fTerms: 'بتسجيل الدخول فإنك توافق على سياسة التدريب التعاوني في كلية الجبيل الصناعية وشروط نظام حماية البيانات الشخصية.',
  fDemo: 'استخدم حسابًا تجريبيًا', fDemoHint: 'تجريبي: أي كلمة مرور تعمل مع الحسابات النموذجية، مثل ahmed.almousa@jic.edu.sa.',
  eEmail: 'أدخل بريدك الإلكتروني، مثل name@jic.edu.sa.', eNoUser: 'لا يوجد حساب في E-COOP بهذا البريد. تحقق منه أو استخدم حسابًا تجريبيًا.', ePass: 'أدخل كلمة المرور.', eSuspended: 'هذا الحساب موقوف. تواصل مع مكتب التدريب التعاوني لإعادة تفعيله.',
  footBuilt: 'من تطوير طلاب إدارة الأعمال في كلية الجبيل الصناعية.', footFor: 'نظام إدارة التدريب التعاوني.',
  footPlatform: 'المنصة', footAccounts: 'الحسابات', footRights: '\u00a9 2026 E-COOP · كلية الجبيل الصناعية. جميع الحقوق محفوظة.',
  accStudent: 'الطلاب', accCompany: 'جهات التدريب', accSupervisor: 'المشرفون', accAdmin: 'مكتب التدريب التعاوني', langSwitch: 'English'
});

Object.assign(LANDING.en, {
  navContact: 'Contact', home: 'Home', footContact: 'Contact', contactUs: 'Contact Us', sendFeedback: 'Send Feedback', reportProblem: 'Report a Problem',
  pFeaturesLead: 'Six tools that cover a COOP term from the first posting to the final evaluation.',
  pHowLead: 'Four stages every placement goes through, and who acts at each one.',
  pRolesLead: 'What students, companies, academic supervisors and the COOP office can each do.',
  exploreTitle: 'Explore E-COOP', learnMore: 'Learn more',
  cTitle: 'Contact the COOP office', cLead: 'Send a question, a partnership request or a problem report. The COOP office reads every message.',
  cName: 'Full name', cNamePh: 'Enter your full name', cEmail: 'Email', cEmailPh: 'Enter your email', cTopic: 'Topic', cMsg: 'Message', cMsgPh: 'Write your message',
  tGeneral: 'General question', tPartner: 'Company partnership', tProblem: 'Technical problem', tFeedback: 'Feedback',
  cSend: 'Send Message', cSentTitle: 'Message sent', cSentBody: 'Your message reached the COOP office inbox. In this demo it appears in the COOP Office account\u2019s notifications.', cAnother: 'Send another message',
  eName: 'Enter your full name.', eMsg: 'Write at least 20 characters so the office can help.', msgLeft: '{n} more characters needed'
});
Object.assign(LANDING.ar, {
  navContact: 'تواصل معنا', home: 'الرئيسية', footContact: 'تواصل', contactUs: 'تواصل معنا', sendFeedback: 'أرسل ملاحظاتك', reportProblem: 'أبلغ عن مشكلة',
  pFeaturesLead: 'ست أدوات تغطي فصل التدريب من أول فرصة منشورة حتى التقييم النهائي.',
  pHowLead: 'أربع مراحل يمر بها كل تدريب، ومن يتولى كل مرحلة.',
  pRolesLead: 'ما يستطيع الطلاب وجهات التدريب والمشرفون الأكاديميون ومكتب التدريب القيام به.',
  exploreTitle: 'تعرّف على E-COOP', learnMore: 'اعرف المزيد',
  cTitle: 'تواصل مع مكتب التدريب التعاوني', cLead: 'أرسل سؤالًا أو طلب شراكة أو بلاغًا عن مشكلة. يقرأ مكتب التدريب كل رسالة.',
  cName: 'الاسم الكامل', cNamePh: 'أدخل اسمك الكامل', cEmail: 'البريد الإلكتروني', cEmailPh: 'أدخل بريدك الإلكتروني', cTopic: 'الموضوع', cMsg: 'الرسالة', cMsgPh: 'اكتب رسالتك',
  tGeneral: 'سؤال عام', tPartner: 'شراكة مع جهة تدريب', tProblem: 'مشكلة تقنية', tFeedback: 'ملاحظات',
  cSend: 'إرسال الرسالة', cSentTitle: 'تم إرسال الرسالة', cSentBody: 'وصلت رسالتك إلى بريد مكتب التدريب. في هذه النسخة التجريبية تظهر في إشعارات حساب مكتب التدريب.', cAnother: 'إرسال رسالة أخرى',
  eName: 'أدخل اسمك الكامل.', eMsg: 'اكتب 20 حرفًا على الأقل ليتمكن المكتب من مساعدتك.', msgLeft: 'مطلوب {n} أحرف إضافية'
});

Object.assign(LANDING.en, {
  createAccount: 'Create Account', newHere: 'New to E-COOP?', haveAccount: 'Already have an account?', signInLink: 'Sign in',
  rTitle: 'Create your E\u2011COOP account', rLead: 'Students, academic supervisors and companies can register with any email. Companies post training opportunities after the COOP office approves them.',
  rType: 'I am a', rStudent: 'Student', rCompany: 'Company', rFullName: 'Full name', rCompanyName: 'Company name', rEmail: 'Email', rCollegeEmail: 'College email',
  rPhone: 'Mobile number', rMajor: 'Major', rGpa: 'GPA (out of 5)', rCity: 'City', rPass: 'Password', rPass2: 'Confirm password',
  rPassHelp: 'At least 8 characters, with a letter and a number.', rAgree: 'I agree to the cooperative training policy and the PDPL personal data terms.', rSubmit: 'Create Account',
  rCompanyNote: 'Company accounts can sign in right away. You can post opportunities once the COOP office approves your company.',
  reName: 'Enter your full name.', reCompany: 'Enter the company name.', reCollege: 'Use your college email ending in @jic.edu.sa.', reEmail: 'Enter a valid email, for example name@example.com.',
  reTaken: 'An account already uses this email. Sign in instead.', rePhone: 'Enter a Saudi mobile number starting with 05, 10 digits.', reGpa: 'GPA must be between 0 and 5.',
  rePass: 'Use at least 8 characters, with a letter and a number.', rePass2: 'The passwords don\u2019t match.', reAgree: 'Agree to the terms to create your account.',
  rePassWrong: 'The password is incorrect. Try again.', rSupervisor: 'Supervisor', rAdmin: 'Admin', rCode: 'Admin access code', rCodeHelp: '', reCode: 'The access code is incorrect. Ask the COOP office for the current code.', rAdmNote: 'Admin accounts can approve companies, manage users and see every record. Only COOP office staff should create one, using the office\u2019s access code.', rDept: 'Department', rCityPh: 'Choose a city', rCityHelp: 'All Saudi cities, grouped by region.', reCity: 'Choose the city where the company is based.', rSupNote: 'Supervisor accounts are for academic staff. The COOP office assigns students to you after you join.', rDemoNote: 'In this demo, new accounts are saved only in this browser.'
});
Object.assign(LANDING.ar, {
  createAccount: 'إنشاء حساب', newHere: 'جديد في E-COOP؟', haveAccount: 'لديك حساب؟', signInLink: 'سجّل الدخول',
  rTitle: 'أنشئ حسابك في E-COOP', rLead: 'يمكن للطلاب والمشرفين الأكاديميين والجهات التسجيل بأي بريد إلكتروني. وتنشر الجهات فرص التدريب بعد اعتمادها من مكتب التدريب التعاوني.',
  rType: 'نوع الحساب', rStudent: 'طالب', rCompany: 'جهة تدريب', rFullName: 'الاسم الكامل', rCompanyName: 'اسم الجهة', rEmail: 'البريد الإلكتروني', rCollegeEmail: 'البريد الجامعي',
  rPhone: 'رقم الجوال', rMajor: 'التخصص', rGpa: 'المعدل (من 5)', rCity: 'المدينة', rPass: 'كلمة المرور', rPass2: 'تأكيد كلمة المرور',
  rPassHelp: '8 أحرف على الأقل، تتضمن حرفًا ورقمًا.', rAgree: 'أوافق على سياسة التدريب التعاوني وشروط نظام حماية البيانات الشخصية.', rSubmit: 'إنشاء الحساب',
  rCompanyNote: 'يمكن لحساب الجهة تسجيل الدخول فورًا، ونشر الفرص بعد اعتماد مكتب التدريب للجهة.',
  reName: 'أدخل اسمك الكامل.', reCompany: 'أدخل اسم الجهة.', reCollege: 'استخدم بريدك الجامعي المنتهي بـ @jic.edu.sa.', reEmail: 'أدخل بريدًا صحيحًا، مثل name@example.com.',
  reTaken: 'هذا البريد مستخدم في حساب آخر. سجّل الدخول بدلًا من ذلك.', rePhone: 'أدخل رقم جوال سعودي يبدأ بـ 05 من 10 أرقام.', reGpa: 'يجب أن يكون المعدل بين 0 و5.',
  rePass: 'استخدم 8 أحرف على الأقل تتضمن حرفًا ورقمًا.', rePass2: 'كلمتا المرور غير متطابقتين.', reAgree: 'وافق على الشروط لإنشاء حسابك.',
  rePassWrong: 'كلمة المرور غير صحيحة. حاول مرة أخرى.', rSupervisor: 'مشرف أكاديمي', rAdmin: 'مسؤول', rCode: 'رمز صلاحية المسؤول', rCodeHelp: '', reCode: 'رمز الصلاحية غير صحيح. اطلب الرمز الحالي من مكتب التدريب التعاوني.', rAdmNote: 'يستطيع المسؤول اعتماد الجهات وإدارة المستخدمين والاطلاع على جميع السجلات. يجب أن يُنشئ هذا الحساب موظفو مكتب التدريب فقط باستخدام رمز الصلاحية.', rDept: 'القسم', rCityPh: 'اختر المدينة', rCityHelp: 'جميع مدن المملكة مرتبة حسب المنطقة.', reCity: 'اختر المدينة التي يقع فيها مقر الجهة.', rSupNote: 'حسابات المشرفين مخصصة لأعضاء هيئة التدريس، ويعيّن مكتب التدريب الطلاب لك بعد انضمامك.', rDemoNote: 'في هذه النسخة التجريبية تُحفظ الحسابات الجديدة في هذا المتصفح فقط.'
});

/* passwords for new accounts are stored as a salted SHA-256 hash, never as text */
function hashPassword(pw, salt) {
  var text = salt + ':' + pw;
  try {
    return crypto.subtle.digest('SHA-256', new TextEncoder().encode(text)).then(function (buf) {
      return Array.prototype.map.call(new Uint8Array(buf), function (b) { return ('0' + b.toString(16)).slice(-2); }).join('');
    });
  } catch (e) {
    var hsh = 2166136261; for (var i = 0; i < text.length; i++) { hsh ^= text.charCodeAt(i); hsh = Math.imul(hsh, 16777619) >>> 0; }
    return Promise.resolve('f' + hsh.toString(16));
  }
}

var LANDING_HISTORY = [];
var HOME_PAGES = ['features', 'how', 'roles', 'contact', 'register'];
function Landing(props) {
  var R = React, h = R.createElement, C = window.COOP;
  var lang = props.lang, L = LANDING[lang], t = props.t;
  var page = R.useState(function () { var x = (location.hash || '').slice(1); return x === 'signin' ? 'home' : HOME_PAGES.indexOf(x) >= 0 ? x : 'intro'; });
  function show(p) { page[1](p); try { history.replaceState(null, '', p === 'intro' ? location.pathname + location.search : p === 'home' ? '#signin' : '#' + p); } catch (er) {} window.scrollTo(0, 0); }
  /* every move records the page you came from, so Back returns there */
  function go(p, topic) { return function (e) { if (e) e.preventDefault(); if (p !== page[0]) LANDING_HISTORY.push(page[0]); if (topic) contactTopic[1](topic); show(p); }; }
  function back(e) { if (e) e.preventDefault(); show(LANDING_HISTORY.pop() || 'intro'); }
  var contactTopic = R.useState('general');
  function Icon(name) { return h('svg', { className: 'l-icon', viewBox: '0 0 24 24', 'aria-hidden': true }, h('path', { d: LICONS[name] })); }

  /* sign-in card */
  var email = R.useState(''), pass = R.useState(''), tried = R.useState(false), err = R.useState(null);
  var emailOk = /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email[0].trim());
  var eEmail = tried[0] && !emailOk ? L.eEmail : null, ePass = tried[0] && !pass[0] ? L.ePass : null;
  function submit(e) {
    e.preventDefault(); tried[1](true); err[1](null);
    if (!emailOk || !pass[0]) return;
    var u = props.users.filter(function (x) { return x.email.toLowerCase() === email[0].trim().toLowerCase(); })[0];
    if (!u) { err[1](L.eNoUser); return; }
    if (u.status !== 'Active') { err[1](L.eSuspended); return; }
    if (!u.pwHash) { props.signIn(u.id); return; }
    hashPassword(pass[0], u.pwSalt).then(function (hv) { if (hv === u.pwHash) props.signIn(u.id); else err[1](L.rePassWrong); });
  }

  var cur = page[0];
  function navLink(p, label) { return h('a', { href: '#' + p, onClick: go(p), 'aria-current': cur === p ? 'page' : undefined }, label); }
  var nav = h('header', { className: 'h-nav' },
    h('div', { className: 'l-wrap h-nav-in' },
      h('a', { className: 'h-brand', href: '#', onClick: go('intro') }, h(props.Mark, { size: 34 }), h('span', null, 'E-COOP')),
      h('nav', { className: 'h-links', 'aria-label': 'Pages' }, navLink('features', L.navFeatures), navLink('how', L.navHow), navLink('roles', L.navRoles), navLink('contact', L.navContact)),
      h('div', { className: 'h-actions' },
        h('button', { className: 'h-text-btn', onClick: props.toggleLang, lang: lang === 'ar' ? 'en' : 'ar' }, L.langSwitch),
        cur !== 'home' ? h('a', { className: 'h-text-btn h-signup', href: '#signin', onClick: go('home') }, L.fSubmit) : null,
        h('a', { className: 'h-btn h-btn-sm h-signup-btn', href: '#register', onClick: go('register'), 'aria-current': cur === 'register' ? 'page' : undefined }, L.createAccount))));

  function band(eyebrow, title, lead) {
    return h('section', { className: 'h-band' }, h('div', { className: 'l-wrap' },
      h('nav', { className: 'h-crumbs', 'aria-label': 'Breadcrumb' }, h('a', { href: '#', className: 'back-pill', onClick: back }, h('span', { 'aria-hidden': true, className: 'back-arrow' }, lang === 'ar' ? '\u2192' : '\u2190'), L.backHome), h('span', { className: 'h-crumb-here', 'aria-current': 'page' }, eyebrow)),
      h('h1', { className: 'h-band-title' }, title), lead ? h('p', { className: 'h-band-lead' }, lead) : null));
  }

  var feats = [['board', 'f1t', 'f1d'], ['apps', 'f2t', 'f2d'], ['code', 'f3t', 'f3d'], ['report', 'f4t', 'f4d'], ['eval', 'f5t', 'f5d'], ['chart', 'f6t', 'f6d']];
  var roles = [['rStudent', 'rStudentL'], ['rCompany', 'rCompanyL'], ['rSupervisor', 'rSupervisorL'], ['rAdmin', 'rAdminL']];

  var body;
  if (cur === 'features') body = h(R.Fragment, null, band(L.navFeatures, L.featuresTitle, L.pFeaturesLead),
    h('section', { className: 'l-sec' }, h('div', { className: 'l-wrap' }, h('div', { className: 'l-feats' }, feats.map(function (f) {
      return h('article', { key: f[0], className: 'l-feat' }, h('span', { className: 'l-icon-wrap' }, Icon(f[0])), h('h2', { className: 'l-h3' }, L[f[1]]), h('p', null, L[f[2]]));
    })))));
  else if (cur === 'how') body = h(R.Fragment, null, band(L.navHow, L.howTitle, L.pHowLead),
    h('section', { className: 'l-sec' }, h('div', { className: 'l-wrap' }, h('ol', { className: 'l-steps' }, [1, 2, 3, 4].map(function (n) {
      return h('li', { key: n, className: 'l-step' }, h('span', { className: 'l-step-n' }, n), h('h2', { className: 'l-h3' }, L['s' + n + 't']), h('p', null, L['s' + n + 'd']));
    })))));
  else if (cur === 'roles') body = h(R.Fragment, null, band(L.navRoles, L.rolesTitle, L.pRolesLead),
    h('section', { className: 'l-sec' }, h('div', { className: 'l-wrap' }, h('div', { className: 'l-roles' }, roles.map(function (r) {
      return h('article', { key: r[0], className: 'l-role' }, h('h2', { className: 'l-h3' }, L[r[0]]), h('ul', null, L[r[1]].map(function (x) { return h('li', { key: x }, x); })));
    })))));
  else if (cur === 'register') body = h(R.Fragment, null, band(L.createAccount, L.rTitle, L.rLead),
    h('section', { className: 'l-sec' }, h('div', { className: 'l-wrap' }, h(RegisterForm, { L: L, lang: lang, users: props.users, onCreate: props.register, goSignIn: go('home') }))));
  else if (cur === 'contact') body = h(R.Fragment, null, band(L.navContact, L.cTitle, L.cLead),
    h('section', { className: 'l-sec' }, h('div', { className: 'l-wrap' }, h(ContactForm, { L: L, topic: contactTopic[0], setTopic: contactTopic[1], onSend: props.sendContact }))));
  else body = h(R.Fragment, null,
    h('section', { className: 'h-hero', id: 'top' },
      h('div', { className: 'l-wrap h-hero-in' },
        h('div', { className: 'h-copy' },
          h('p', { className: 'h-eyebrow' }, L.hEyebrow),
          h('h1', { className: 'h-title' }, L.hTitle),
          h('p', { className: 'h-lead' }, L.hLead)),
        h('form', { className: 'h-card', onSubmit: submit, noValidate: true, 'aria-label': L.fSubmit },
          err[0] ? h(C.Alert, { tone: 'danger', title: err[0] }) : null,
          h('div', { className: 'h-field' },
            h('label', { htmlFor: 'h-email' }, L.fEmail, h('span', { 'aria-hidden': true }, '*')),
            h('input', { id: 'h-email', type: 'email', autoComplete: 'username', placeholder: L.fEmailPh, value: email[0], 'aria-invalid': eEmail ? 'true' : undefined, 'aria-describedby': eEmail ? 'h-email-err' : undefined, onChange: function (e) { email[1](e.target.value); err[1](null); } }),
            eEmail ? h('p', { className: 'h-err', id: 'h-email-err' }, eEmail) : null),
          h('div', { className: 'h-field' },
            h('label', { htmlFor: 'h-pass' }, L.fPass, h('span', { 'aria-hidden': true }, '*')),
            h('input', { id: 'h-pass', type: 'password', autoComplete: 'current-password', placeholder: L.fPassPh, value: pass[0], 'aria-invalid': ePass ? 'true' : undefined, 'aria-describedby': ePass ? 'h-pass-err' : undefined, onChange: function (e) { pass[1](e.target.value); } }),
            ePass ? h('p', { className: 'h-err', id: 'h-pass-err' }, ePass) : null),
          h('button', { type: 'submit', className: 'h-btn h-btn-block' }, L.fSubmit),
          h('p', { className: 'h-terms' }, L.fTerms),
          h('p', { className: 'h-new' }, L.newHere + ' ', h('a', { href: '#register', className: 'h-link', onClick: go('register') }, L.createAccount)),
          h('div', { className: 'h-demo' }, h('button', { type: 'button', className: 'h-link', onClick: props.openApp }, L.fDemo), h('p', null, L.fDemoHint))))),
    h('section', { className: 'l-sec' }, h('div', { className: 'l-wrap' },
      h('h2', { className: 'l-h2 h-explore-title' }, L.exploreTitle),
      h('div', { className: 'h-explore' }, [['features', 'board', L.navFeatures, L.pFeaturesLead], ['how', 'apps', L.navHow, L.pHowLead], ['roles', 'eval', L.navRoles, L.pRolesLead]].map(function (x) {
        return h('a', { key: x[0], href: '#' + x[0], className: 'h-explore-card', onClick: go(x[0]) }, h('span', { className: 'l-icon-wrap' }, Icon(x[1])), h('span', { className: 'l-h3' }, x[2]), h('span', { className: 'h-explore-text' }, x[3]), h('span', { className: 'h-explore-more' }, L.learnMore + (lang === 'ar' ? ' \u2190' : ' \u2192')));
      })))));

  var foot = h('footer', { className: 'h-foot' },
    h('div', { className: 'l-wrap' },
      h('div', { className: 'h-foot-top' },
        h('div', { className: 'h-foot-brand' },
          h('div', { className: 'h-brand' }, h(props.Mark, { size: 34 }), h('span', null, 'E-COOP')),
          h('p', null, L.footBuilt), h('p', null, L.footFor)),
        h('div', { className: 'h-foot-col' }, h('p', { className: 'h-foot-title' }, L.footPlatform),
          h('a', { href: '#features', onClick: go('features') }, L.navFeatures), h('a', { href: '#how', onClick: go('how') }, L.navHow), h('a', { href: '#roles', onClick: go('roles') }, L.navRoles)),
        h('div', { className: 'h-foot-col' }, h('p', { className: 'h-foot-title' }, L.footContact),
          h('a', { href: '#contact', onClick: go('contact', 'general') }, L.contactUs),
          h('a', { href: '#contact', onClick: go('contact', 'feedback') }, L.sendFeedback),
          h('a', { href: '#contact', onClick: go('contact', 'problem') }, L.reportProblem))),
      h('div', { className: 'h-foot-bottom' },
        h('p', null, L.footRights),
        h('button', { className: 'h-foot-link', onClick: props.toggleLang }, L.langSwitch))));

  if (cur === 'intro') return h('div', { className: 'landing home intro coop-root' },
    h('div', { className: 'l-top' },
      h('header', { className: 'l-nav' },
        h('div', { className: 'l-wrap l-nav-in' },
          h('a', { className: 'l-brand', href: '#', onClick: go('intro') }, h(props.Mark, { size: 32 }), h('span', null, 'E-COOP')),
          h('nav', { className: 'l-links', 'aria-label': 'Pages' },
            h('a', { href: '#features', onClick: go('features') }, L.navFeatures),
            h('a', { href: '#how', onClick: go('how') }, L.navHow),
            h('a', { href: '#roles', onClick: go('roles') }, L.navRoles)),
          h('div', { className: 'l-nav-actions' },
            h('button', { className: 'l-pill l-pill-ghost l-pill-sm', onClick: props.toggleLang, lang: lang === 'ar' ? 'en' : 'ar' }, L.langSwitch),
            h('button', { className: 'l-pill l-pill-sm', onClick: go('home') }, L.fSubmit)))),
      h('main', { className: 'l-hero' },
        h('div', { className: 'l-wrap l-hero-in' },
          h('div', { className: 'l-hero-text' },
            h('span', { className: 'l-appicon' }, h(props.Mark, { size: 72 })),
            h('p', { className: 'l-eyebrow' }, L.heroEyebrow),
            h('h1', { className: 'l-h1' }, L.heroTitle),
            h('p', { className: 'l-lead' }, L.heroLead),
            h('div', { className: 'l-ctas' },
              h('button', { className: 'l-pill', onClick: go('home') }, L.getStarted + (lang === 'ar' ? ' ←' : ' →')),
              h('button', { className: 'l-pill l-pill-ghost', onClick: go('how') }, L.seeHow)),
            h('ul', { className: 'l-facts' },
              h('li', null, h('b', null, '4'), ' ', L.factRoles), h('li', null, h('b', null, '2'), ' ', L.factLangs), h('li', null, h('b', null, '6'), ' ', L.factCode))),
          h('div', { className: 'l-hero-demo', 'aria-hidden': true },
            h('div', { className: 'l-demo-a' }, h(C.DailyCodeCard, { code: '482915', status: 'Active', title: lang === 'ar' ? 'رمز حضور اليوم' : 'Today’s attendance code', meta: lang === 'ar' ? 'ينتهي 23:59 اليوم · ⁦7 / 9⁩ سجّلوا الحضور' : 'Expires 23:59 today · 7 / 9 trainees checked in' })),
            h('div', { className: 'l-demo-b' }, h(C.OpportunityCard, { title: 'Supply Chain Analyst Trainee', company: 'Eastern Logistics Co.', city: 'Dammam', majors: 'Business Administration', status: 'Accepted', labels: { city: t('city'), majors: t('majors'), capacity: t('seats') } })),
            h('div', { className: 'l-demo-c' }, ['Approved', 'Pending Approval', 'Submitted'].map(function (s) { return h(C.StatusBadge, { key: s, status: s }, lang === 'ar' ? STATUS_AR[s] : s); })))))));
  return h('div', { className: 'landing home coop-root' }, nav, h('main', { key: cur }, body), foot);
}

function ContactForm(p) {
  var R = React, h = R.createElement, C = window.COOP, L = p.L;
  var v = R.useState({ name: '', email: '', msg: '' }), tried = R.useState(false), sent = R.useState(false);
  var x = v[0], emailOk = /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(x.email.trim()), left = Math.max(0, 20 - x.msg.trim().length);
  var errs = { name: x.name.trim().length < 3 ? L.eName : null, email: !emailOk ? L.eEmail : null, msg: left ? L.eMsg : null };
  function set(k) { return function (e) { var n = Object.assign({}, x); n[k] = e.target.value; v[1](n); }; }
  function field(k, label, input, help) {
    var e = tried[0] ? errs[k] : null;
    return h('div', { className: 'h-field' }, h('label', { htmlFor: 'c-' + k }, label, h('span', { 'aria-hidden': true }, '*')), input(e), e ? h('p', { className: 'h-err', id: 'c-' + k + '-err' }, e) : help ? h('p', { className: 'h-help' }, help) : null);
  }
  if (sent[0]) return h('div', { className: 'h-card h-card-wide h-sent', role: 'status' },
    h(C.Alert, { tone: 'success', title: L.cSentTitle }, L.cSentBody),
    h('button', { className: 'h-btn', onClick: function () { v[1]({ name: '', email: '', msg: '' }); tried[1](false); sent[1](false); } }, L.cAnother));
  var topics = [['general', L.tGeneral], ['partner', L.tPartner], ['problem', L.tProblem], ['feedback', L.tFeedback]];
  return h('form', { className: 'h-card h-card-wide', noValidate: true, onSubmit: function (e) {
      e.preventDefault(); tried[1](true);
      if (errs.name || errs.email || errs.msg) return;
      p.onSend({ name: x.name.trim(), email: x.email.trim(), topic: topics.filter(function (t) { return t[0] === p.topic; })[0][1], msg: x.msg.trim() });
      sent[1](true);
    } },
    h('div', { className: 'h-row2' },
      field('name', L.cName, function (e) { return h('input', { id: 'c-name', autoComplete: 'name', placeholder: L.cNamePh, value: x.name, onChange: set('name'), 'aria-invalid': e ? 'true' : undefined, 'aria-describedby': e ? 'c-name-err' : undefined }); }),
      field('email', L.cEmail, function (e) { return h('input', { id: 'c-email', type: 'email', autoComplete: 'email', placeholder: L.cEmailPh, value: x.email, onChange: set('email'), 'aria-invalid': e ? 'true' : undefined, 'aria-describedby': e ? 'c-email-err' : undefined }); })),
    h('div', { className: 'h-field' }, h('span', { className: 'h-label', id: 'c-topic-l' }, L.cTopic),
      h('div', { className: 'chips', role: 'radiogroup', 'aria-labelledby': 'c-topic-l' }, topics.map(function (tp) {
        return h('button', { key: tp[0], type: 'button', role: 'radio', 'aria-checked': p.topic === tp[0] ? 'true' : 'false', className: 'chip', 'aria-pressed': p.topic === tp[0] ? 'true' : 'false', onClick: function () { p.setTopic(tp[0]); } }, tp[1]);
      }))),
    field('msg', L.cMsg, function (e) { return h('textarea', { id: 'c-msg', rows: 6, placeholder: L.cMsgPh, value: x.msg, onChange: set('msg'), 'aria-invalid': e ? 'true' : undefined, 'aria-describedby': e ? 'c-msg-err' : undefined }); }, left ? L.msgLeft.replace('{n}', left) : null),
    h('button', { type: 'submit', className: 'h-btn h-btn-block' }, L.cSend));
}

var REG_MAJORS = ['Business Administration', 'Chemical Engineering', 'Industrial Engineering', 'Mechanical Engineering', 'Electrical Engineering', 'Computer Science'];
var REG_CITIES = ['Jubail', 'Dammam', 'Khobar', 'Riyadh', 'Ras Tanura'];
var REG_DEPTS = ['Business Administration', 'Chemical Engineering Technology', 'Industrial Engineering Technology', 'Mechanical Engineering Technology', 'Electrical Engineering Technology', 'Computer Science'];
function RegisterForm(p) {
  var R = React, h = R.createElement, C = window.COOP, L = p.L;
  var kind = R.useState('Student'), busy = R.useState(false), tried = R.useState(false), touched = R.useState({});
  var v = R.useState({ name: '', email: '', phone: '', major: REG_MAJORS[0], gpa: '', city: '', dept: REG_DEPTS[0], code: '', pass: '', pass2: '', agree: false });
  var x = v[0], isStu = kind[0] === 'Student', isSup = kind[0] === 'Supervisor', isAdm = kind[0] === 'Admin', college = isStu || isSup || isAdm;
  var em = x.email.trim().toLowerCase();
  var errs = {};
  if (x.name.trim().length < 3) errs.name = college ? L.reName : L.reCompany;
  if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(em)) errs.email = L.reEmail;
  else if (p.users.some(function (u) { return u.email.toLowerCase() === em; })) errs.email = L.reTaken;
  if (!/^05\d{8}$/.test(x.phone.replace(/\s/g, ''))) errs.phone = L.rePhone;
  if (kind[0] === 'Company' && !x.city) errs.city = L.reCity;
  if (isStu) { var g = parseFloat(x.gpa); if (x.gpa === '' || isNaN(g) || g < 0 || g > 5) errs.gpa = L.reGpa; }
  if (x.pass.length < 8 || !/[A-Za-z\u0600-\u06FF]/.test(x.pass) || !/\d/.test(x.pass)) errs.pass = L.rePass;
  if (x.pass2 !== x.pass || !x.pass2) errs.pass2 = L.rePass2;
  if (!x.agree) errs.agree = L.reAgree;
  function show(k) { return (tried[0] || touched[0][k]) ? errs[k] : null; }
  function set(k, val) { var n = Object.assign({}, x); n[k] = val; v[1](n); }
  function blur(k) { return function () { var tt = Object.assign({}, touched[0]); tt[k] = true; touched[1](tt); }; }
  function input(k, label, attrs, help) {
    var e = show(k);
    return h('div', { className: 'h-field' },
      h('label', { htmlFor: 'r-' + k }, label, h('span', { 'aria-hidden': true }, '*')),
      h(attrs.options ? 'select' : 'input', Object.assign({ id: 'r-' + k, value: x[k], onChange: function (ev) { set(k, ev.target.value); }, onBlur: blur(k), 'aria-invalid': e ? 'true' : undefined, 'aria-describedby': e ? 'r-' + k + '-err' : undefined }, attrs.options ? {} : attrs),
        attrs.options ? attrs.options.map(function (o) { return h('option', { key: o, value: o }, o); }) : undefined),
      e ? h('p', { className: 'h-err', id: 'r-' + k + '-err' }, e) : help ? h('p', { className: 'h-help' }, help) : null);
  }
  function citySelect() {
    var e = show('city'), ar = p.lang === 'ar';
    return h('div', { className: 'h-field' },
      h('label', { htmlFor: 'r-city' }, L.rCity, h('span', { 'aria-hidden': true }, '*')),
      h('select', { id: 'r-city', value: x.city, onChange: function (ev) { set('city', ev.target.value); }, onBlur: blur('city'), 'aria-invalid': e ? 'true' : undefined, 'aria-describedby': e ? 'r-city-err' : 'r-city-help' },
        h('option', { value: '', disabled: true }, L.rCityPh),
        SA_REGIONS.map(function (rg) {
          var items = rg[2].slice().sort(function (a, b) { return (ar ? a[1] : a[0]).localeCompare(ar ? b[1] : b[0], ar ? 'ar' : 'en'); });
          return h('optgroup', { key: rg[0], label: ar ? rg[1] : rg[0] }, items.map(function (c) { return h('option', { key: c[0], value: c[0] }, ar ? c[1] : c[0]); }));
        })),
      e ? h('p', { className: 'h-err', id: 'r-city-err' }, e) : h('p', { className: 'h-help', id: 'r-city-help' }, L.rCityHelp));
  }
  function submit(ev) {
    ev.preventDefault(); tried[1](true);
    if (Object.keys(errs).length || busy[0]) return;
    busy[1](true);
    var salt = Math.random().toString(36).slice(2) + Date.now().toString(36);
    hashPassword(x.pass, salt).then(function (hv) {
      p.onCreate({ kind: kind[0], name: x.name.trim(), email: em, phone: x.phone.replace(/\s/g, ''), major: x.major, gpa: isStu ? String(parseFloat(x.gpa)) : '', city: x.city, dept: x.dept, pwHash: hv, pwSalt: salt });
    });
  }
  return h('form', { className: 'h-card h-card-wide', noValidate: true, onSubmit: submit },
    h('div', { className: 'h-field' }, h('span', { className: 'h-label', id: 'r-kind-l' }, L.rType),
      h('div', { className: 'seg', role: 'radiogroup', 'aria-labelledby': 'r-kind-l' }, [['Student', L.rStudent], ['Supervisor', L.rSupervisor], ['Company', L.rCompany]].map(function (k) {
        return h('button', { key: k[0], type: 'button', role: 'radio', className: 'seg-btn', 'aria-checked': kind[0] === k[0] ? 'true' : 'false', onClick: function () { kind[1](k[0]); } }, k[1]);
      }))),
    kind[0] === 'Company' ? h(C.Alert, { tone: 'info' }, L.rCompanyNote) : isAdm ? h(C.Alert, { tone: 'warning' }, L.rAdmNote) : isSup ? h(C.Alert, { tone: 'info' }, L.rSupNote) : null,
    h('div', { className: 'h-row2' },
      input('name', college ? L.rFullName : L.rCompanyName, { autoComplete: college ? 'name' : 'organization' }),
      input('email', L.rEmail, { type: 'email', autoComplete: 'email', placeholder: 'name@example.com' })),
    h('div', { className: 'h-row2' },
      input('phone', L.rPhone, { inputMode: 'numeric', autoComplete: 'tel', placeholder: '05XXXXXXXX' }),
      isStu ? input('major', L.rMajor, { options: REG_MAJORS }) : isSup ? input('dept', L.rDept, { options: REG_DEPTS }) : isAdm ? input('code', L.rCode, { autoComplete: 'off', placeholder: 'XXXX-0000' }, L.rCodeHelp) : citySelect()),
    isStu ? h('div', { className: 'h-row2' }, input('gpa', L.rGpa, { inputMode: 'decimal', placeholder: '4.25' }), h('span')) : null,
    h('div', { className: 'h-row2' },
      input('pass', L.rPass, { type: 'password', autoComplete: 'new-password' }, L.rPassHelp),
      input('pass2', L.rPass2, { type: 'password', autoComplete: 'new-password' })),
    h('div', { className: 'h-field' },
      h('label', { className: 'h-check' }, h('input', { type: 'checkbox', id: 'r-agree', checked: x.agree, onChange: function (ev) { set('agree', ev.target.checked); }, 'aria-invalid': show('agree') ? 'true' : undefined }), h('span', null, L.rAgree)),
      show('agree') ? h('p', { className: 'h-err' }, errs.agree) : null),
    h('button', { type: 'submit', className: 'h-btn h-btn-block', disabled: busy[0] }, L.rSubmit),
    h('p', { className: 'h-new' }, L.haveAccount + ' ', h('a', { href: '#', className: 'h-link', onClick: p.goSignIn }, L.signInLink)),
    h('p', { className: 'h-help' }, L.rDemoNote));
}
