/* Seed data - fictional sample records for the demo. Today is fixed so the demo stays consistent. */
var TODAY = '2026-09-30';
/* contact numbers for non-student accounts (students keep theirs on the student record) */
var USER_PHONES = { u9: '0138001200', u10: '0133401100', u11: '0133620044', u12: '0138995500', u13: '0551002003', u14: '0552003004', u15: '0133409000' };
function isWorkday(d) { var w = new Date(d + 'T12:00:00Z').getUTCDay(); return w >= 0 && w <= 4; } /* Sun-Thu */
function addDays(d, n) { var x = new Date(d + 'T12:00:00Z'); x.setUTCDate(x.getUTCDate() + n); return x.toISOString().slice(0, 10); }
function workdays(from, to) { var out = [], d = from; while (d <= to) { if (isWorkday(d)) out.push(d); d = addDays(d, 1); } return out; }

function seed() {
  var s = {
    users: [
      { id: 'u1', name: 'Ahmed Abdulmohsen Almousa', email: 'ahmed.almousa@jic.edu.sa', role: 'Student', status: 'Active', ref: 's1' },
      { id: 'u2', name: 'Anas Ahmed Alamri', email: 'anas.alamri@jic.edu.sa', role: 'Student', status: 'Active', ref: 's2' },
      { id: 'u3', name: 'Omar Abdulaziz Alghamdi', email: 'omar.alghamdi@jic.edu.sa', role: 'Student', status: 'Active', ref: 's3' },
      { id: 'u4', name: 'Hamad Ali Alghubari', email: 'hamad.alghubari@jic.edu.sa', role: 'Student', status: 'Active', ref: 's4' },
      { id: 'u5', name: 'Abdullah Al-Ghamdi', email: 'abdullah.alghamdi@jic.edu.sa', role: 'Student', status: 'Active', ref: 's5' },
      { id: 'u6', name: 'Yazeed Mubarak Alqahtani', email: 'yazeed.alqahtani@jic.edu.sa', role: 'Student', status: 'Active', ref: 's6' },
      { id: 'u7', name: 'Hind Al-Zahrani', email: 'hind.alzahrani@jic.edu.sa', role: 'Student', status: 'Active', ref: 's7' },
      { id: 'u8', name: 'Turki Al-Anazi', email: 'turki.alanazi@jic.edu.sa', role: 'Student', status: 'Suspended', ref: 's8' },
      { id: 'u9', name: 'Eastern Logistics Co.', email: 'coop@easternlogistics.sa', role: 'Company', status: 'Active', ref: 'c2' },
      { id: 'u10', name: 'Gulf Petrochemical Co.', email: 'training@gulfpetrochem.sa', role: 'Company', status: 'Active', ref: 'c1' },
      { id: 'u11', name: 'Al-Bahar Industrial Services', email: 'hr@albahar-is.sa', role: 'Company', status: 'Active', ref: 'c3' },
      { id: 'u12', name: 'Khobar Digital Solutions', email: 'careers@khobardigital.sa', role: 'Company', status: 'Active', ref: 'c4' },
      { id: 'u13', name: 'Abdullah Rashed Alrumi', email: 'abdullah.alrumi@jic.edu.sa', role: 'Supervisor', status: 'Active', ref: 'sup1' },
      { id: 'u14', name: 'Dr. Mona Al-Ghamdi', email: 'm.alghamdi@jic.edu.sa', role: 'Supervisor', status: 'Active', ref: 'sup2' },
      { id: 'u15', name: 'COOP Office', email: 'coop.office@jic.edu.sa', role: 'Admin', status: 'Active', ref: 'a1' }
    ],
    students: [
      { id: 's1', user: 'u1', major: 'Business Administration', gpa: '4.21', phone: '0551234567', cv: 'Ahmed_Almousa_CV.pdf', supervisor: 'sup1', placement: 'p1' },
      { id: 's2', user: 'u2', major: 'Business Administration', gpa: '4.62', phone: '0559876543', cv: 'Anas_Alamri_CV.pdf', supervisor: 'sup1', placement: 'p1' },
      { id: 's3', user: 'u3', major: 'Business Administration', gpa: '3.78', phone: '0543219876', cv: 'Omar_Alghamdi_CV.docx', supervisor: 'sup1', placement: 'p1' },
      { id: 's4', user: 'u4', major: 'Chemical Engineering', gpa: '4.05', phone: '0567788990', cv: 'Hamad_Alghubari_CV.pdf', supervisor: 'sup1', placement: 'p2' },
      { id: 's5', user: 'u5', major: 'Industrial Engineering', gpa: '3.64', phone: '0501122334', cv: 'Abdullah_CV.pdf', supervisor: 'sup2', placement: 'p2' },
      { id: 's6', user: 'u6', major: 'Business Administration', gpa: '4.40', phone: '0534455667', cv: 'Yazeed_Alqahtani_CV.pdf', supervisor: null, placement: null },
      { id: 's7', user: 'u7', major: 'Business Administration', gpa: '3.92', phone: '0578899001', cv: 'Hind_CV.pdf', supervisor: 'sup2', placement: null },
      { id: 's8', user: 'u8', major: 'Mechanical Engineering', gpa: '3.10', phone: '0512233445', cv: '', supervisor: 'sup2', placement: null }
    ],
    companies: [
      { id: 'c1', user: 'u10', name: 'Gulf Petrochemical Co.', city: 'Jubail', status: 'Approved' },
      { id: 'c2', user: 'u9', name: 'Eastern Logistics Co.', city: 'Dammam', status: 'Approved' },
      { id: 'c3', user: 'u11', name: 'Al-Bahar Industrial Services', city: 'Jubail', status: 'Pending Approval' },
      { id: 'c4', user: 'u12', name: 'Khobar Digital Solutions', city: 'Khobar', status: 'Pending Approval' }
    ],
    opps: [
      { id: 'o1', company: 'c1', title: 'Process Engineering Trainee', city: 'Jubail', majors: 'Chemical Engineering, Industrial Engineering', capacity: 4, status: 'Open', posted: '2026-08-18', description: 'Support the operations team with process monitoring, shift logs and daily production reports.' },
      { id: 'o2', company: 'c2', title: 'Supply Chain Analyst Trainee', city: 'Dammam', majors: 'Business Administration', capacity: 3, status: 'Open', posted: '2026-08-12', description: 'Track inbound shipments, reconcile warehouse stock counts and prepare weekly KPI summaries.' },
      { id: 'o3', company: 'c1', title: 'Maintenance Planning Trainee', city: 'Jubail', majors: 'Mechanical Engineering', capacity: 3, status: 'Open', posted: '2026-09-02', description: 'Schedule preventive maintenance work orders and follow up spare-part requests with stores.' },
      { id: 'o4', company: 'c2', title: 'HR Operations Trainee', city: 'Dammam', majors: 'Business Administration', capacity: 2, status: 'Open', posted: '2026-09-14', description: 'Help run onboarding, update employee records and prepare attendance reports for payroll.' },
      { id: 'o5', company: 'c1', title: 'Quality Control Lab Trainee', city: 'Jubail', majors: 'Chemistry, Chemical Engineering', capacity: 2, status: 'Pending Approval', posted: '2026-09-27', description: 'Run routine sample tests, record results in the lab system and flag out-of-spec batches.' },
      { id: 'o6', company: 'c2', title: 'Customs and Trade Compliance Trainee', city: 'Dammam', majors: 'Business Administration', capacity: 1, status: 'Pending Approval', posted: '2026-09-28', description: 'Prepare import documentation, check HS codes and track customs clearance status.' },
      { id: 'o7', company: 'c2', title: 'Fleet Scheduling Trainee', city: 'Khobar', majors: 'Business Administration, Industrial Engineering', capacity: 2, status: 'Open', posted: '2026-09-20', description: 'Plan daily delivery routes and monitor driver schedules against service-level targets.' }
    ],
    apps: [
      { id: 'a1', student: 's1', opp: 'o2', status: 'Accepted', date: '2026-08-20' },
      { id: 'a2', student: 's2', opp: 'o2', status: 'Accepted', date: '2026-08-19' },
      { id: 'a3', student: 's3', opp: 'o2', status: 'Accepted', date: '2026-08-22' },
      { id: 'a4', student: 's1', opp: 'o1', status: 'Rejected', date: '2026-08-19' },
      { id: 'a5', student: 's4', opp: 'o1', status: 'Accepted', date: '2026-08-21' },
      { id: 'a6', student: 's5', opp: 'o1', status: 'Accepted', date: '2026-08-24' },
      { id: 'a7', student: 's7', opp: 'o4', status: 'Submitted', date: '2026-09-22' },
      { id: 'a8', student: 's7', opp: 'o7', status: 'Submitted', date: '2026-09-24' },
      { id: 'a9', student: 's8', opp: 'o4', status: 'Rejected', date: '2026-09-16' }
    ],
    placements: [
      { id: 'p1', opp: 'o2', start: '2026-09-06', end: '2026-12-24', status: 'Active' },
      { id: 'p2', opp: 'o1', start: '2026-09-06', end: '2026-12-24', status: 'Active' }
    ],
    codes: [
      { id: 'k1', placement: 'p1', code: '107364', date: '2026-09-29', status: 'Expired' },
      { id: 'k2', placement: 'p1', code: '482915', date: TODAY, status: 'Active' },
      { id: 'k3', placement: 'p2', code: '730258', date: TODAY, status: 'Active' }
    ],
    attendance: [],
    reports: [
      { id: 'r1', student: 's2', file: 'Anas_Alamri_Final_Report.pdf', date: '2026-09-28', status: 'Submitted' },
      { id: 'r2', student: 's4', file: 'Hamad_Alghubari_Final_Report.docx', date: '2026-09-25', status: 'Request Revision' }
    ],
    evals: [
      { id: 'e1', student: 's4', type: 'Company', by: 'u10', score: 88, comments: 'Reliable on shift handovers; logs are accurate and on time.', date: '2026-09-24' },
      { id: 'e2', student: 's2', type: 'Company', by: 'u9', score: 92, comments: 'Built the weekly KPI sheet the warehouse team now uses.', date: '2026-09-27' },
      { id: 'e3', student: 's3', type: 'Company', by: 'u9', score: 54, comments: 'Missed three weekly reporting deadlines and needs closer follow-up on stock counts. Improved in the last week.', date: '2026-09-28' }
    ],
    audit: [
      { id: 'l1', user: 'u15', action: 'Approved company Eastern Logistics Co.', ts: '2026-08-10T09:12' },
      { id: 'l2', user: 'u15', action: 'Approved opportunity Supply Chain Analyst Trainee', ts: '2026-08-12T10:40' },
      { id: 'l3', user: 'u15', action: 'Assigned Abdullah Rashed Alrumi to Ahmed Abdulmohsen Almousa', ts: '2026-09-01T08:05' },
      { id: 'l4', user: 'u15', action: 'Suspended account Turki Al-Anazi', ts: '2026-09-18T13:22' },
      { id: 'l5', user: 'u9', action: 'Generated daily code for Supply Chain Analyst Trainee', ts: '2026-09-30T07:30' }
    ],
    notifs: [
      { id: 'n1', user: 'u1', key: 'nAttApproved', args: { d: '2026-09-27' }, ts: '2026-09-28T09:10', read: false },
      { id: 'n2', user: 'u1', key: 'nCodeReminder', args: {}, ts: '2026-09-30T07:30', read: false },
      { id: 'n3', user: 'u1', key: 'nAppAccepted', args: { o: 'Supply Chain Analyst Trainee' }, ts: '2026-08-25T11:02', read: true },
      { id: 'n4', user: 'u6', key: 'nWelcome', args: {}, ts: '2026-09-29T10:00', read: false },
      { id: 'n5', user: 'u9', key: 'nNewApp', args: { n: 'Hind Al-Zahrani', o: 'HR Operations Trainee' }, ts: '2026-09-22T13:40', read: false },
      { id: 'n6', user: 'u9', key: 'nOppPending', args: { o: 'Customs and Trade Compliance Trainee' }, ts: '2026-09-28T09:00', read: true },
      { id: 'n7', user: 'u13', key: 'nReportIn', args: { n: 'Anas Ahmed Alamri' }, ts: '2026-09-28T16:20', read: false },
      { id: 'n8', user: 'u13', key: 'nAttPending', args: { c: 8 }, ts: '2026-09-30T08:00', read: false },
      { id: 'n9', user: 'u15', key: 'nCompanyPending', args: { n: 'Khobar Digital Solutions' }, ts: '2026-09-29T12:15', read: false },
      { id: 'n10', user: 'u15', key: 'nOppNew', args: { o: 'Customs and Trade Compliance Trainee' }, ts: '2026-09-28T09:00', read: false }
    ],
    chats: [
      { id: 'ch1', kind: 'group', title: 'Supply Chain Analyst Trainee · Eastern Logistics', members: ['u9', 'u1', 'u2', 'u3', 'u13'] },
      { id: 'ch2', kind: 'direct', members: ['u1', 'u13'] },
      { id: 'ch3', kind: 'direct', members: ['u9', 'u13'] },
      { id: 'ch4', kind: 'direct', members: ['u6', 'u15'] },
      { id: 'ch5', kind: 'direct', members: ['u2', 'u13'] },
      { id: 'ch6', kind: 'direct', members: ['u1', 'u9'] }
    ],
    msgs: [
      { id: 'm1', chat: 'ch1', from: 'u9', text: 'Good morning all. Today\u2019s attendance code is on the screen next to reception.', ts: '2026-09-30T07:31' },
      { id: 'm2', chat: 'ch1', from: 'u2', text: 'Thank you, checked in.', ts: '2026-09-30T07:47' },
      { id: 'm3', chat: 'ch1', from: 'u9', text: 'Reminder: the weekly KPI review moves to Thursday at 10:00 in meeting room 2.', ts: '2026-09-30T08:05' },
      { id: 'm4', chat: 'ch1', from: 'u13', text: 'I will join the Thursday review for the first 30 minutes to meet the team.', ts: '2026-09-30T08:20' },
      { id: 'm5', chat: 'ch2', from: 'u13', text: 'Ahmed, please share an outline of your final report by 8 October so I can comment early.', ts: '2026-09-28T10:02' },
      { id: 'm6', chat: 'ch2', from: 'u1', text: 'Sure, Mr. Abdullah. Should the report follow the college template or the company one?', ts: '2026-09-28T10:15' },
      { id: 'm7', chat: 'ch2', from: 'u13', text: 'Use the college template, and add the company KPIs as an appendix.', ts: '2026-09-28T10:21' },
      { id: 'm8', chat: 'ch3', from: 'u13', text: 'Could I schedule a site visit for my three trainees next week?', ts: '2026-09-27T12:40' },
      { id: 'm9', chat: 'ch3', from: 'u9', text: 'Tuesday 6 October at 11:00 works for us. Please bring your college ID for the gate.', ts: '2026-09-27T13:05' },
      { id: 'm10', chat: 'ch4', from: 'u6', text: 'When will new opportunities for Business Administration open?', ts: '2026-09-29T09:30' },
      { id: 'm11', chat: 'ch4', from: 'u15', text: 'Two new postings are waiting for approval this week. You will get a notification when they go live.', ts: '2026-09-29T11:12' },
      { id: 'm12', chat: 'ch5', from: 'u2', text: 'I uploaded my final report. Please let me know if anything needs changes.', ts: '2026-09-28T16:22' },
      { id: 'm13', chat: 'ch6', from: 'u9', text: 'Ahmed, please bring the stock count sheets from warehouse B tomorrow morning.', ts: '2026-09-29T15:10' }
    ],
    reads: { 'u1:ch2': '2026-09-28T10:15', 'u13:ch2': '2026-09-28T10:21', 'u9:ch1': '2026-09-30T08:05', 'u13:ch1': '2026-09-30T08:20', 'u13:ch3': '2026-09-27T13:05', 'u9:ch3': '2026-09-27T13:05', 'u15:ch4': '2026-09-29T11:12', 'u6:ch4': '2026-09-29T09:30', 'u9:ch6': '2026-09-29T15:10' },
    prefs: {},
    seq: 100
  };
  /* attendance history: p1 and p2 trainees, workdays since start. A few absences; last two days pending approval. */
  var absent = { s1: ['2026-09-15', '2026-09-23'], s2: [], s3: ['2026-09-10', '2026-09-17', '2026-09-24'], s4: ['2026-09-21'], s5: ['2026-09-08', '2026-09-22'] };
  var n = 0;
  ['s1', 's2', 's3', 's4', 's5'].forEach(function (sid) {
    var st = s.students.filter(function (x) { return x.id === sid; })[0];
    workdays('2026-09-06', '2026-09-29').forEach(function (d) {
      if (absent[sid].indexOf(d) >= 0) return;
      var mins = 40 + ((sid.charCodeAt(1) * 7 + d.charCodeAt(9) * 13) % 19);
      s.attendance.push({ id: 't' + (++n), student: sid, placement: st.placement, date: d, time: '07:' + (mins < 10 ? '0' : '') + mins, status: d >= '2026-09-28' ? 'Pending' : 'Approved' });
    });
  });
  s.attendance.push({ id: 't' + (++n), student: 's2', placement: 'p1', date: TODAY, time: '07:46', status: 'Pending' });
  s.attendance.push({ id: 't' + (++n), student: 's4', placement: 'p2', date: TODAY, time: '07:51', status: 'Pending' });
  return s;
}
