(function () {
    'use strict';

    const STORAGE_KEY = 'isteDemyoDalisData';
    const ACTIVE_STUDENT_KEY = 'isteDemyoActiveStudentId';
    const ACTIVE_STAFF_KEY = 'isteDemyoActiveStaffId';
    const ACTIVE_ADMIN_KEY = 'isteDemyoActiveAdminId';
    const REPORT_IDS_KEY = 'isteDemyoReportDiveIds';
    const REPORT_ABOUT_TEXT = 'Bu doküman, İskenderun Teknik Üniversitesi Denizcilik Teknolojileri MYO dalış kayıt sisteminde onaylanan dalış kayıtlarının öğrenci bazında raporlanması için hazırlanmıştır. Rapor; kapak, doküman bilgisi, dalış özeti ve dalış amiri onay alanlarını içerir. Çift taraflı yazdırma için sayfa düzeni ön ve arka yüzlerde sayfa numarası alt bilgisiyle hazırlanmıştır.';

    const sampleData = {
        students: [],
        staff: [],
        admins: [
            {
                id: 1,
                admin_no: '3001',
                password: '',
                password_hash: 'c9b018966de06d7ac7a5aba21cd4f14b096aa0697ea97b3e6da73fcc5fb80d10',
                name: 'Sistem',
                surname: 'Yonetici',
                role_name: 'admin',
                approval_status: 'approved'
            }
        ],
        dives: []
    };

    document.addEventListener('DOMContentLoaded', function () {
        ensureData();
        bindGlobalActions();

        const page = document.body.dataset.page;
        if (page === 'student-login') initStudentLogin();
        if (page === 'staff-login') initStaffLogin();
        if (page === 'admin-login') initAdminLogin();
        if (page === 'student-dashboard') initStudentDashboard();
        if (page === 'student-dive-entry') initDiveEntry();
        if (page === 'staff-dashboard') initStaffDashboard();
        if (page === 'admin-dashboard') initAdminDashboard();
        if (page === 'student-report') initReport();
        if (page === 'backup') initBackup();
    });

    function ensureData(forceReset) {
        if (forceReset) {
            saveData(sampleData);
            return;
        }

        if (!readStoredData()) {
            saveData(sampleData);
        }
    }

    function getData() {
        ensureData(false);
        const data = JSON.parse(readStoredData());
        if (normalizeData(data)) {
            saveData(data);
        }
        return data;
    }

    function saveData(data) {
        const serialized = JSON.stringify(data);
        localStorage.setItem(STORAGE_KEY, serialized);

        const request = new XMLHttpRequest();
        request.open('PUT', '/api/data', false);
        request.setRequestHeader('Content-Type', 'application/json');
        try {
            request.send(serialized);
        } catch (error) {
            localStorage.setItem(STORAGE_KEY, serialized);
        }
    }

    function readStoredData() {
        const request = new XMLHttpRequest();
        request.open('GET', '/api/data', false);
        try {
            request.send();
            if (request.status >= 200 && request.status < 300 && request.responseText) {
                localStorage.setItem(STORAGE_KEY, request.responseText);
                return request.responseText;
            }
        } catch (error) {
            return localStorage.getItem(STORAGE_KEY);
        }

        return localStorage.getItem(STORAGE_KEY);
    }

    function normalizeData(data) {
        let changed = false;
        data.students.forEach(function (student) {
            if (!student.approval_status) {
                student.approval_status = 'approved';
                changed = true;
            }
            if (student.class_name === undefined) {
                student.class_name = '';
                changed = true;
            }
        });
        data.staff.forEach(function (staff) {
            if (!staff.approval_status) {
                staff.approval_status = 'approved';
                changed = true;
            }
            if (staff.email === undefined) {
                staff.email = '';
                changed = true;
            }
        });
        data.admins.forEach(function (admin) {
            if (admin.id === 1 && !admin.password_hash) {
                admin.password = '';
                admin.password_hash = 'c9b018966de06d7ac7a5aba21cd4f14b096aa0697ea97b3e6da73fcc5fb80d10';
                changed = true;
            }
        });
        data.students.forEach(function (student) {
            if (!student.tc_no) {
                student.tc_no = '';
                changed = true;
            }
        });
        data.dives.forEach(function (dive) {
            if (!dive.approval_status) {
                dive.approval_status = 'approved';
                changed = true;
            }
            if (dive.approval_status === 'approved' && !dive.approved_at) {
                dive.approved_at = dive.dive_date;
                changed = true;
            }
            if (dive.approval_status === 'approved' && dive.approved_by_staff_id === undefined) {
                dive.approved_by_staff_id = null;
                changed = true;
            }
            if (dive.approval_status === 'rejected') {
                if (dive.rejected_by_staff_id === undefined) {
                    dive.rejected_by_staff_id = null;
                    changed = true;
                }
                if (dive.rejection_reason === undefined) {
                    dive.rejection_reason = '';
                    changed = true;
                }
            }
        });
        if (!Array.isArray(data.admins)) {
            data.admins = sampleData.admins.slice();
            changed = true;
        }
        if (!Array.isArray(data.students)) {
            data.students = [];
            changed = true;
        }
        if (!Array.isArray(data.staff)) {
            data.staff = [];
            changed = true;
        }
        if (!Array.isArray(data.dives)) {
            data.dives = [];
            changed = true;
        }
        return changed;
    }

    function bindGlobalActions() {
        document.querySelectorAll('[data-reset-data]').forEach(function (button) {
            button.addEventListener('click', function () {
                ensureData(true);
                localStorage.removeItem(REPORT_IDS_KEY);
                localStorage.removeItem(ACTIVE_ADMIN_KEY);
                alert('Kayıt verileri sıfırlandı.');
                window.location.reload();
            });
        });

        document.querySelectorAll('[data-logout]').forEach(function (link) {
            link.addEventListener('click', function () {
                localStorage.removeItem(ACTIVE_STUDENT_KEY);
                localStorage.removeItem(ACTIVE_STAFF_KEY);
                localStorage.removeItem(ACTIVE_ADMIN_KEY);
            });
        });
    }

    function initStudentLogin() {
        const form = document.getElementById('studentLoginForm');
        const registerForm = document.getElementById('studentRegisterForm');
        const message = document.getElementById('loginMessage');
        const registerMessage = document.getElementById('registerMessage');

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const data = getData();
            const formData = new FormData(form);
            const student = data.students.find(function (item) {
                return item.student_no === formData.get('student_no');
            });

            if (!student || !await passwordMatches(formData.get('password'), student)) {
                showMessage(message, 'Öğrenci no veya şifre hatalı.');
                return;
            }

            if (!isAccountApproved(student)) {
                showMessage(message, 'Kaydınız henüz sistem yöneticisi tarafından onaylanmadı.');
                return;
            }

            localStorage.setItem(ACTIVE_STUDENT_KEY, String(student.id));
            localStorage.removeItem(ACTIVE_STAFF_KEY);
            localStorage.removeItem(ACTIVE_ADMIN_KEY);
            window.location.href = 'student-dashboard.html';
        });

        if (!registerForm) return;
        registerForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const data = getData();
            const formData = new FormData(registerForm);
            const studentNo = readString(formData, 'student_no');
            const email = readString(formData, 'email').toLowerCase();
            const password = readString(formData, 'password');

            if (!isIsteEmail(email)) {
                showMessage(registerMessage, 'Sadece iste.edu.tr uzantılı e-posta kabul edilir.');
                return;
            }
            if (data.students.some(function (item) { return item.student_no === studentNo || normalize(item.email) === normalize(email); })) {
                showMessage(registerMessage, 'Bu öğrenci no veya e-posta zaten kayıtlı.');
                return;
            }

            data.students.push({
                id: nextId(data.students),
                student_no: studentNo,
                password: '',
                password_hash: await digestPassword(password),
                name: readString(formData, 'name'),
                surname: readString(formData, 'surname'),
                program: readString(formData, 'program'),
                class_name: readString(formData, 'class_name'),
                tc_no: '',
                phone: readString(formData, 'phone'),
                email: email,
                approval_status: 'pending',
                created_at: new Date().toISOString()
            });
            saveData(data);
            registerForm.reset();
            showMessage(registerMessage, 'Kaydınız alındı. Yönetici onayından sonra giriş yapabilirsiniz.', true);
        });
    }

    function initStaffLogin() {
        const form = document.getElementById('staffLoginForm');
        const registerForm = document.getElementById('staffRegisterForm');
        const message = document.getElementById('loginMessage');
        const registerMessage = document.getElementById('registerMessage');

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const data = getData();
            const formData = new FormData(form);
            const staff = data.staff.find(function (item) {
                return item.staff_no === formData.get('staff_no');
            });

            if (!staff || !await passwordMatches(formData.get('password'), staff)) {
                showMessage(message, 'Kurum no veya şifre hatalı.');
                return;
            }

            if (!isAccountApproved(staff)) {
                showMessage(message, 'Kaydınız henüz sistem yöneticisi tarafından onaylanmadı.');
                return;
            }

            localStorage.setItem(ACTIVE_STAFF_KEY, String(staff.id));
            localStorage.removeItem(ACTIVE_STUDENT_KEY);
            localStorage.removeItem(ACTIVE_ADMIN_KEY);
            window.location.href = 'staff-dashboard.html';
        });

        if (!registerForm) return;
        registerForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            const data = getData();
            const formData = new FormData(registerForm);
            const staffNo = readString(formData, 'staff_no');
            const email = readString(formData, 'email').toLowerCase();

            if (!isIsteEmail(email)) {
                showMessage(registerMessage, 'Sadece iste.edu.tr uzantılı e-posta kabul edilir.');
                return;
            }
            if (data.staff.some(function (item) { return item.staff_no === staffNo || normalize(item.email) === normalize(email); })) {
                showMessage(registerMessage, 'Bu kurum no veya e-posta zaten kayıtlı.');
                return;
            }

            data.staff.push({
                id: nextId(data.staff),
                staff_no: staffNo,
                password: '',
                password_hash: await digestPassword(readString(formData, 'password')),
                name: readString(formData, 'name'),
                surname: readString(formData, 'surname'),
                email: email,
                role_name: 'staff',
                display_role: 'Dalış Amiri',
                approval_status: 'pending',
                created_at: new Date().toISOString()
            });
            saveData(data);
            registerForm.reset();
            showMessage(registerMessage, 'Kaydınız alındı. Yönetici onayından sonra giriş yapabilirsiniz.', true);
        });
    }

    function initAdminLogin() {
        const form = document.getElementById('adminLoginForm');
        const message = document.getElementById('loginMessage');

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const data = getData();
            const formData = new FormData(form);
            const admin = data.admins.find(function (item) {
                return item.admin_no === formData.get('admin_no');
            });

            if (!admin || !await passwordMatches(formData.get('password'), admin)) {
                showMessage(message, 'Yönetici no veya şifre hatalı.');
                return;
            }

            localStorage.setItem(ACTIVE_ADMIN_KEY, String(admin.id));
            localStorage.removeItem(ACTIVE_STUDENT_KEY);
            localStorage.removeItem(ACTIVE_STAFF_KEY);
            window.location.href = 'admin-dashboard.html';
        });
    }

    function initStudentDashboard() {
        const data = getData();
        const student = getActiveStudent(data);
        const dives = getStudentDives(data, student.id);
        const approvedDives = dives.filter(isApproved);
        const stats = buildStats(approvedDives);

        setText('studentName', fullName(student));
        setText('studentIdentity', student.student_no + ' - ' + student.program + ' - TC: ' + displayTcNo(student));
        setText('totalDiveCount', String(stats.count));
        setText('totalDiveDuration', stats.duration + ' dk');
        setText('maxDepth', formatNumber(stats.maxDepth) + ' m');
        setText('lastDiveDate', stats.lastDate ? formatDate(stats.lastDate) : '-');
        renderStudentMessages(dives);

        const rows = dives.map(function (dive) {
            return '<tr>' +
                '<td>' + escapeHtml(formatDate(dive.dive_date)) + '</td>' +
                '<td>' + escapeHtml(dive.location) + '</td>' +
                '<td>' + escapeHtml(dive.dive_type) + '</td>' +
                '<td>' + escapeHtml(dive.duration_minutes + ' dk') + '</td>' +
                '<td>' + escapeHtml(formatNumber(dive.max_depth) + ' m') + '</td>' +
                '<td>' + escapeHtml(dive.supervisor_name || '-') + '</td>' +
                '<td>' + statusBadge(dive) + rejectionNote(dive) + '</td>' +
            '</tr>';
        }).join('');

        document.getElementById('studentDiveRows').innerHTML = rows || '<tr><td class="empty-row" colspan="7">Henüz dalış kaydı yok.</td></tr>';
    }

    function initDiveEntry() {
        const data = getData();
        const student = getActiveStudent(data);
        const form = document.getElementById('diveEntryForm');
        const message = document.getElementById('entryMessage');

        document.getElementById('entryStudentNo').value = student.student_no;
        document.getElementById('entryStudentName').value = fullName(student);

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const currentData = getData();
            const currentStudent = getActiveStudent(currentData);
            const formData = new FormData(form);
            const dive = {
                id: nextId(currentData.dives),
                student_id: currentStudent.id,
                dive_date: readString(formData, 'dive_date'),
                location: readString(formData, 'location'),
                dive_type: readString(formData, 'dive_type'),
                purpose: readString(formData, 'purpose'),
                start_time: readString(formData, 'start_time'),
                end_time: readString(formData, 'end_time'),
                duration_minutes: readNumber(formData, 'duration_minutes'),
                max_depth: readNumber(formData, 'max_depth'),
                water_temperature: readNumber(formData, 'water_temperature'),
                visibility: readNumber(formData, 'visibility'),
                weather: readString(formData, 'weather'),
                equipment: readString(formData, 'equipment'),
                start_pressure: readNumber(formData, 'start_pressure'),
                end_pressure: readNumber(formData, 'end_pressure'),
                supervisor_name: readString(formData, 'supervisor_name'),
                notes: readString(formData, 'notes'),
                approval_status: 'pending',
                submitted_at: new Date().toISOString(),
                approved_by_staff_id: null,
                approved_at: null
            };

            currentData.dives.push(dive);
            saveData(currentData);
            showMessage(message, 'Dalış kaydı dalış amiri onayına gönderildi. Öğrenci paneline yönlendiriliyorsunuz.', true);
            setTimeout(function () {
                window.location.href = 'student-dashboard.html';
            }, 700);
        });
    }

    function initStaffDashboard() {
        const data = getData();
        const staff = getActiveStaff(data);
        const allDives = sortedDives(data.dives);
        const approvedDives = allDives.filter(isApproved);
        const pendingDives = allDives.filter(isPending);
        const stats = buildStats(approvedDives);

        setText('staffIdentity', fullName(staff) + ' - ' + staff.display_role);
        setText('staffStudentCount', String(data.students.length));
        setText('staffDiveCount', String(approvedDives.length));
        setText('staffDuration', stats.duration + ' dk');
        setText('staffPendingCount', String(pendingDives.length));

        renderPendingApprovals(data, staff, pendingDives);
        renderStaffStudents(data);
        renderStaffDives(data, allDives);
        initStaffFilters(data);
        initReportSelector(data);
    }

    function initAdminDashboard() {
        const data = getData();
        const admin = getActiveAdmin(data);

        setText('adminIdentity', fullName(admin) + ' - Sistem yöneticisi');
        renderAdminDashboard(data);
        bindAdminStudentForm();
        bindAdminStaffForm();
    }

    function renderAdminDashboard(data) {
        setText('adminStudentCount', String(data.students.length));
        setText('adminStaffCount', String(data.staff.length));
        setText('adminAdminCount', String(data.admins.length));
        setText('adminDiveCount', String(data.dives.length));
        renderAdminStudents(data);
        renderAdminStaff(data);
    }

    function bindAdminStudentForm() {
        const form = document.getElementById('adminStudentForm');
        const message = document.getElementById('adminStudentMessage');

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const data = getData();
            const formData = new FormData(form);
            const studentNo = readString(formData, 'student_no');
            const email = readString(formData, 'email').toLowerCase();

            if (data.students.some(function (student) { return student.student_no === studentNo; })) {
                showMessage(message, 'Bu öğrenci no zaten kayıtlı.');
                return;
            }
            if (!isIsteEmail(email)) {
                showMessage(message, 'Sadece iste.edu.tr uzantılı e-posta kabul edilir.');
                return;
            }

            data.students.push({
                id: nextId(data.students),
                student_no: studentNo,
                password: '',
                password_hash: await digestPassword(readString(formData, 'password')),
                name: readString(formData, 'name'),
                surname: readString(formData, 'surname'),
                program: readString(formData, 'program'),
                class_name: readString(formData, 'class_name'),
                tc_no: readString(formData, 'tc_no'),
                phone: readString(formData, 'phone'),
                email: email,
                approval_status: 'approved',
                approved_at: new Date().toISOString()
            });

            saveData(data);
            form.reset();
            showMessage(message, 'Öğrenci eklendi. Öğrenci giriş ekranından kullanılabilir.', true);
            renderAdminDashboard(data);
        });
    }

    function bindAdminStaffForm() {
        const form = document.getElementById('adminStaffForm');
        const message = document.getElementById('adminStaffMessage');

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const data = getData();
            const formData = new FormData(form);
            const staffNo = readString(formData, 'staff_no');
            const email = readString(formData, 'email').toLowerCase();

            if (data.staff.some(function (staff) { return staff.staff_no === staffNo; })) {
                showMessage(message, 'Bu kurum no zaten kayıtlı.');
                return;
            }
            if (!isIsteEmail(email)) {
                showMessage(message, 'Sadece iste.edu.tr uzantılı e-posta kabul edilir.');
                return;
            }

            data.staff.push({
                id: nextId(data.staff),
                staff_no: staffNo,
                password: '',
                password_hash: await digestPassword(readString(formData, 'password')),
                name: readString(formData, 'name'),
                surname: readString(formData, 'surname'),
                email: email,
                role_name: 'staff',
                display_role: readString(formData, 'display_role'),
                approval_status: 'approved',
                approved_at: new Date().toISOString()
            });

            saveData(data);
            form.reset();
            showMessage(message, 'Öğretim elemanı / dalış amiri eklendi. Staff giriş ekranından kullanılabilir.', true);
            renderAdminDashboard(data);
        });
    }

    function renderAdminStudents(data) {
        const rows = data.students.map(function (student) {
            return '<tr>' +
                '<td>' + escapeHtml(student.student_no) + '</td>' +
                '<td>' + escapeHtml(fullName(student)) + '</td>' +
                '<td>' + escapeHtml(student.program) + '</td>' +
                '<td>' + escapeHtml(displayTcNo(student)) + '</td>' +
                '<td>' + escapeHtml(student.email || '-') + '</td>' +
                '<td>' + accountStatusBadge(student) + '</td>' +
                '<td><div class="row-actions">' +
                    (isAccountApproved(student) ? '' : '<button class="btn primary" type="button" data-approve-student="' + student.id + '">Onayla</button>') +
                    '<button class="btn danger" type="button" data-delete-student="' + student.id + '">Çıkar</button>' +
                '</div></td>' +
            '</tr>';
        }).join('');

        const tbody = document.getElementById('adminStudentRows');
        tbody.innerHTML = rows || '<tr><td class="empty-row" colspan="7">Öğrenci bulunmuyor.</td></tr>';
        tbody.querySelectorAll('[data-approve-student]').forEach(function (button) {
            button.addEventListener('click', function () {
                const currentData = getData();
                const student = currentData.students.find(function (item) { return item.id === Number(button.dataset.approveStudent); });
                if (!student) return;
                student.approval_status = 'approved';
                student.approved_at = new Date().toISOString();
                saveData(currentData);
                renderAdminDashboard(currentData);
            });
        });
        tbody.querySelectorAll('[data-delete-student]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!window.confirm('Bu öğrenciyi sistemden çıkarmak istiyor musunuz?')) return;
                const currentData = getData();
                const studentId = Number(button.dataset.deleteStudent);
                currentData.students = currentData.students.filter(function (item) { return item.id !== studentId; });
                currentData.dives = currentData.dives.filter(function (item) { return item.student_id !== studentId; });
                saveData(currentData);
                renderAdminDashboard(currentData);
            });
        });
    }

    function renderAdminStaff(data) {
        const rows = data.staff.map(function (staff) {
            return '<tr>' +
                '<td>' + escapeHtml(staff.staff_no) + '</td>' +
                '<td>' + escapeHtml(fullName(staff)) + '</td>' +
                '<td>' + escapeHtml(staff.display_role || 'Öğretim Elemanı / Dalış Amiri') + '</td>' +
                '<td>' + escapeHtml(staff.email || '-') + '</td>' +
                '<td>' + accountStatusBadge(staff) + '</td>' +
                '<td>' + (isAccountApproved(staff) ? '' : '<button class="btn primary" type="button" data-approve-staff="' + staff.id + '">Onayla</button>') + '</td>' +
            '</tr>';
        }).join('');

        const tbody = document.getElementById('adminStaffRows');
        tbody.innerHTML = rows || '<tr><td class="empty-row" colspan="6">Staff kullanıcısı bulunmuyor.</td></tr>';
        tbody.querySelectorAll('[data-approve-staff]').forEach(function (button) {
            button.addEventListener('click', function () {
                const currentData = getData();
                const staff = currentData.staff.find(function (item) { return item.id === Number(button.dataset.approveStaff); });
                if (!staff) return;
                staff.approval_status = 'approved';
                staff.approved_at = new Date().toISOString();
                saveData(currentData);
                renderAdminDashboard(currentData);
            });
        });
    }

    function renderPendingApprovals(data, staff, pendingDives) {
        const tbody = document.getElementById('pendingApprovalRows');
        const rows = pendingDives.map(function (dive) {
            const student = data.students.find(function (item) {
                return item.id === dive.student_id;
            });
            return '<tr>' +
                '<td>' + escapeHtml(student ? student.student_no + ' - ' + fullName(student) : '-') + '</td>' +
                '<td>' + escapeHtml(formatDate(dive.dive_date)) + '</td>' +
                '<td>' + escapeHtml(dive.location) + '</td>' +
                '<td>' + escapeHtml(dive.dive_type) + '</td>' +
                '<td>' + escapeHtml(dive.duration_minutes + ' dk') + '</td>' +
                '<td>' + escapeHtml(dive.supervisor_name || '-') + '</td>' +
                '<td><div class="row-actions">' +
                    '<button class="btn primary" type="button" data-approve-dive="' + dive.id + '">Onayla</button>' +
                    '<button class="btn danger" type="button" data-reject-dive="' + dive.id + '">Reddet</button>' +
                '</div></td>' +
            '</tr>';
        }).join('');

        tbody.innerHTML = rows || '<tr><td class="empty-row" colspan="7">Onay bekleyen dalış yok.</td></tr>';
        tbody.querySelectorAll('[data-approve-dive]').forEach(function (button) {
            button.addEventListener('click', function () {
                const currentData = getData();
                const dive = currentData.dives.find(function (item) {
                    return item.id === Number(button.dataset.approveDive);
                });
                if (!dive) return;
                if (!window.confirm('Bu dalış kaydını onaylamak istiyor musunuz?')) return;

                dive.approval_status = 'approved';
                dive.approved_by_staff_id = staff.id;
                dive.approved_at = new Date().toISOString();
                dive.rejection_reason = '';
                dive.rejected_by_staff_id = null;
                dive.rejected_at = null;
                if (!dive.supervisor_name) {
                    dive.supervisor_name = fullName(staff);
                }

                saveData(currentData);
                window.location.reload();
            });
        });
        tbody.querySelectorAll('[data-reject-dive]').forEach(function (button) {
            button.addEventListener('click', function () {
                const reason = window.prompt('Reddetme açıklamasını yazın:');
                if (reason === null) return;
                const cleanReason = reason.trim();
                if (!cleanReason) {
                    alert('Reddetme açıklaması boş bırakılamaz.');
                    return;
                }

                const currentData = getData();
                const dive = currentData.dives.find(function (item) {
                    return item.id === Number(button.dataset.rejectDive);
                });
                if (!dive) return;

                dive.approval_status = 'rejected';
                dive.rejection_reason = cleanReason;
                dive.rejection_message_deleted = false;
                dive.rejected_by_staff_id = staff.id;
                dive.rejected_at = new Date().toISOString();
                dive.approved_by_staff_id = null;
                dive.approved_at = null;

                saveData(currentData);
                window.location.reload();
            });
        });
    }

    function renderStudentMessages(dives) {
        const panel = document.getElementById('studentMessagesPanel');
        const container = document.getElementById('studentMessages');
        if (!panel || !container) return;

        const rejectedDives = dives.filter(function (dive) {
            return dive.approval_status === 'rejected' && !dive.rejection_message_deleted;
        });

        if (!rejectedDives.length) {
            panel.hidden = true;
            return;
        }

        panel.hidden = false;
        container.innerHTML = rejectedDives.map(function (dive) {
            return '<article class="message-item">' +
                '<strong>' + escapeHtml(formatDate(dive.dive_date) + ' - ' + dive.location) + '</strong>' +
                '<span>' + escapeHtml(dive.rejection_reason || 'Dalış kaydı reddedildi.') + '</span>' +
                '<button class="message-delete" type="button" data-delete-message="' + dive.id + '">Mesajı sil</button>' +
            '</article>';
        }).join('');

        container.querySelectorAll('[data-delete-message]').forEach(function (button) {
            button.addEventListener('click', function () {
                const currentData = getData();
                const dive = currentData.dives.find(function (item) {
                    return item.id === Number(button.dataset.deleteMessage);
                });
                if (!dive) return;
                dive.rejection_message_deleted = true;
                saveData(currentData);
                renderStudentMessages(getStudentDives(currentData, dive.student_id));
            });
        });
    }

    function renderStaffStudents(data) {
        const rows = data.students.map(function (student) {
            const dives = getStudentDives(data, student.id).filter(isApproved);
            const stats = buildStats(dives);
            return '<tr>' +
                '<td>' + escapeHtml(student.student_no) + '</td>' +
                '<td>' + escapeHtml(fullName(student)) + '</td>' +
                '<td>' + escapeHtml(student.program) + '</td>' +
                '<td>' + escapeHtml(displayTcNo(student)) + '</td>' +
                '<td>' + escapeHtml(String(stats.count)) + '</td>' +
                '<td>' + escapeHtml(stats.duration + ' dk') + '</td>' +
                '<td><a class="btn ghost" href="student-report.html?student=' + student.id + '">Rapor al</a></td>' +
            '</tr>';
        }).join('');

        document.getElementById('staffStudentRows').innerHTML = rows;
    }

    function initStaffFilters(data) {
        const form = document.getElementById('staffFilterForm');
        const clearButton = document.getElementById('clearFilters');

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            renderStaffDives(data, filterDives(data));
        });

        clearButton.addEventListener('click', function () {
            form.reset();
            renderStaffDives(data, sortedDives(data.dives));
        });
    }

    function renderStaffDives(data, dives) {
        const rows = dives.map(function (dive) {
            const student = data.students.find(function (item) {
                return item.id === dive.student_id;
            });
            return '<tr>' +
                '<td>' + escapeHtml(student ? student.student_no + ' - ' + fullName(student) : '-') + '</td>' +
                '<td>' + escapeHtml(formatDate(dive.dive_date)) + '</td>' +
                '<td>' + escapeHtml(dive.location) + '</td>' +
                '<td>' + escapeHtml(dive.dive_type) + '</td>' +
                '<td>' + escapeHtml(dive.duration_minutes + ' dk') + '</td>' +
                '<td>' + escapeHtml(formatNumber(dive.max_depth) + ' m') + '</td>' +
                '<td>' + escapeHtml(dive.supervisor_name || '-') + '</td>' +
                '<td>' + statusBadge(dive) + '</td>' +
            '</tr>';
        }).join('');

        document.getElementById('staffDiveRows').innerHTML = rows || '<tr><td class="empty-row" colspan="8">Filtreye uygun kayıt bulunamadı.</td></tr>';
    }

    function filterDives(data) {
        const studentNo = normalize(document.getElementById('filterStudentNo').value);
        const name = normalize(document.getElementById('filterName').value);
        const dateFrom = document.getElementById('filterDateFrom').value;
        const dateTo = document.getElementById('filterDateTo').value;
        const location = normalize(document.getElementById('filterLocation').value);
        const type = normalize(document.getElementById('filterType').value);

        return sortedDives(data.dives).filter(function (dive) {
            const student = data.students.find(function (item) {
                return item.id === dive.student_id;
            });
            const studentName = student ? normalize(fullName(student)) : '';
            const matchesStudentNo = !studentNo || (student && normalize(student.student_no).includes(studentNo));
            const matchesName = !name || studentName.includes(name);
            const matchesDateFrom = !dateFrom || dive.dive_date >= dateFrom;
            const matchesDateTo = !dateTo || dive.dive_date <= dateTo;
            const matchesLocation = !location || normalize(dive.location).includes(location);
            const matchesType = !type || normalize(dive.dive_type).includes(type);
            return matchesStudentNo && matchesName && matchesDateFrom && matchesDateTo && matchesLocation && matchesType;
        });
    }

    function initReportSelector(data) {
        const select = document.getElementById('reportStudentSelect');
        const list = document.getElementById('reportDiveList');
        const form = document.getElementById('reportSelectForm');

        select.innerHTML = data.students.map(function (student) {
            return '<option value="' + student.id + '">' + escapeHtml(student.student_no + ' - ' + fullName(student)) + '</option>';
        }).join('');

        function renderList() {
            const studentId = Number(select.value);
            const dives = getStudentDives(data, studentId).filter(isApproved);
            list.innerHTML = dives.map(function (dive) {
                return '<label class="checkbox-item">' +
                    '<input type="checkbox" name="dive_ids" value="' + dive.id + '" checked>' +
                    '<span>' + escapeHtml(formatDate(dive.dive_date) + ' - ' + dive.location + ' - ' + dive.dive_type + ' - ' + dive.duration_minutes + ' dk') + '</span>' +
                '</label>';
            }).join('') || '<p class="muted">Bu öğrenci için onaylı dalış kaydı yok.</p>';
        }

        select.addEventListener('change', renderList);
        renderList();

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const selectedIds = Array.from(form.querySelectorAll('input[name="dive_ids"]:checked')).map(function (input) {
                return Number(input.value);
            });

            if (!selectedIds.length) {
                alert('Rapor için en az bir dalış seçin.');
                return;
            }

            localStorage.setItem(REPORT_IDS_KEY, JSON.stringify(selectedIds));
            window.location.href = 'student-report.html?student=' + encodeURIComponent(select.value) + '&selected=1';
        });
    }

    function initReport() {
        const data = getData();
        const params = new URLSearchParams(window.location.search);
        const studentId = Number(params.get('student')) || Number(localStorage.getItem(ACTIVE_STUDENT_KEY)) || data.students[0].id;
        const student = data.students.find(function (item) {
            return item.id === studentId;
        }) || data.students[0];
        const selectedIds = params.get('selected') === '1' ? readReportIds() : [];
        let dives = getStudentDives(data, student.id).filter(isApproved);

        if (selectedIds.length) {
            dives = dives.filter(function (dive) {
                return selectedIds.includes(dive.id);
            });
        }

        renderReport(data, student, dives);

        document.getElementById('printReport').addEventListener('click', function () {
            window.print();
        });
    }

    function renderReport(data, student, dives) {
        const root = document.getElementById('reportRoot');
        const stats = buildStats(dives);
        const dateRange = dives.length
            ? formatDate(dives[dives.length - 1].dive_date) + ' - ' + formatDate(dives[0].dive_date)
            : '-';

        const summaryRows = dives.map(function (dive) {
            return '<tr>' +
                '<td>' + escapeHtml(formatDate(dive.dive_date)) + '</td>' +
                '<td>' + escapeHtml(dive.location) + '</td>' +
                '<td>' + escapeHtml(dive.dive_type) + '</td>' +
                '<td>' + escapeHtml(dive.duration_minutes + ' dk') + '</td>' +
                '<td>' + escapeHtml(formatNumber(dive.max_depth) + ' m') + '</td>' +
                '<td>' + escapeHtml(supervisorDisplay(data, dive)) + '</td>' +
            '</tr>';
        }).join('');

        const cover = '<section class="report-page cover-page">' +
            '<div class="cover-brand">' +
                '<img src="assets/iste-amblem.png" alt="İskenderun Teknik Üniversitesi amblemi">' +
                '<div>' +
                    '<h1>İSKENDERUN TEKNİK ÜNİVERSİTESİ</h1>' +
                    '<h2>DENİZCİLİK TEKNOLOJİLERİ MYO</h2>' +
                '</div>' +
            '</div>' +
            '<div class="cover-title">' +
                '<h3>DALIŞ KAYIT RAPORU</h3>' +
                '<p>' + escapeHtml(fullName(student)) + '</p>' +
            '</div>' +
            '<div class="report-summary cover-summary">' +
                summaryLine('Öğrenci no', student.student_no) +
                summaryLine('Program', student.program) +
                summaryLine('TC no', displayTcNo(student)) +
                summaryLine('Rapor tarih aralığı', dateRange) +
                summaryLine('Toplam dalış sayısı', String(stats.count)) +
                summaryLine('Rapor oluşturma tarihi', formatDate(new Date().toISOString().slice(0, 10))) +
            '</div>' +
            pageFooter(1) +
        '</section>';

        const about = '<section class="report-page about-page">' +
            '<div class="report-title">' +
                '<h1>Doküman Hakkında</h1>' +
            '</div>' +
            '<p class="about-text">' + escapeHtml(REPORT_ABOUT_TEXT) + '</p>' +
            '<div class="report-summary">' +
                summaryLine('Öğrenci no', student.student_no) +
                summaryLine('Ad soyad', fullName(student)) +
                summaryLine('Program', student.program) +
                summaryLine('TC no', displayTcNo(student)) +
                summaryLine('Rapor tarih aralığı', dateRange) +
                summaryLine('Toplam dalış sayısı', String(stats.count)) +
                summaryLine('Toplam dalış süresi', stats.duration + ' dk') +
                summaryLine('Maksimum derinlik', formatNumber(stats.maxDepth) + ' m') +
                summaryLine('Rapor oluşturma tarihi', formatDate(new Date().toISOString().slice(0, 10))) +
                summaryLine('Raporu oluşturan', 'Öğretim Elemanı / Dalış Amiri') +
            '</div>' +
            pageFooter(2) +
        '</section>';

        const summary = '<section class="report-page summary-page">' +
            '<div class="report-title">' +
                '<h1>Dalış Özeti Raporu</h1>' +
            '</div>' +
            '<table>' +
                '<thead><tr><th>Tarih</th><th>Dalış yeri</th><th>Dalış tipi</th><th>Süre</th><th>Maks. derinlik</th><th>Dalış amiri</th></tr></thead>' +
                '<tbody>' + (summaryRows || '<tr><td class="empty-row" colspan="6">Seçili dalış yok.</td></tr>') + '</tbody>' +
            '</table>' +
            pageFooter(3) +
        '</section>';

        const divePages = chunkItems(dives, 1).map(function (pageDives, pageIndex) {
            return '<section class="report-page dive-approval-page">' +
                '<div class="report-title">' +
                    '<h1>Dalış Onayları</h1>' +
                    '<h2>' + escapeHtml(fullName(student) + ' - ' + student.student_no) + '</h2>' +
                '</div>' +
                '<div class="approval-stack">' +
                    pageDives.map(function (dive) {
                        return renderDiveApproval(data, student, dive);
                    }).join('') +
                '</div>' +
                pageFooter(pageIndex + 4) +
            '</section>';
        }).join('');

        root.innerHTML = cover + about + summary + divePages;
    }

    function renderDiveApproval(data, student, dive) {
        return '<article class="dive-approval-card">' +
            '<table class="dive-detail-table">' +
                detailRow('Öğrenci no', student.student_no, 'Ad soyad', fullName(student)) +
                detailRow('TC no', displayTcNo(student), 'Program', student.program) +
                detailRow('Dalış tarihi', formatDate(dive.dive_date), 'Dalış yeri', dive.location) +
                detailRow('Dalış tipi', dive.dive_type, 'Amaç / görev', dive.purpose) +
                detailRow('Başlama saati', dive.start_time, 'Bitiş saati', dive.end_time) +
                detailRow('Dalış süresi', dive.duration_minutes + ' dk', 'Maksimum derinlik', formatNumber(dive.max_depth) + ' m') +
                detailRow('Su sıcaklığı', formatNumber(dive.water_temperature), 'Görüş mesafesi', formatNumber(dive.visibility)) +
                detailRow('Hava / deniz durumu', dive.weather, 'Ekipman', dive.equipment) +
                detailRow('Başlangıç tüp basıncı', dive.start_pressure, 'Bitiş tüp basıncı', dive.end_pressure) +
                '<tr><th>Açıklama / bulgular</th><td colspan="3">' + escapeHtml(dive.notes || '-') + '</td></tr>' +
            '</table>' +
            '<div class="approval-box">' +
                '<h3>DALIŞ AMİRİ ONAYI</h3>' +
                '<div class="approval-grid">' +
                    '<div class="approval-cell">' + escapeHtml(supervisorDisplay(data, dive)) + '</div>' +
                    '<div class="approval-cell"><strong>İmza</strong></div>' +
                    '<div class="approval-cell"><strong>Tarih</strong><br>' + escapeHtml(formatDate(dive.approved_at || new Date().toISOString().slice(0, 10))) + '</div>' +
                    '<div class="approval-cell"><strong>Kaşe / onay alanı</strong></div>' +
                '</div>' +
            '</div>' +
        '</article>';
    }

    function supervisorDisplay(data, dive) {
        const staff = data.staff.find(function (item) {
            return item.id === dive.approved_by_staff_id;
        });
        if (staff) {
            return fullName(staff) + ' - ' + (staff.display_role || 'Dalış Amiri');
        }
        return dive.supervisor_name || '-';
    }

    function pageFooter(pageNumber) {
        return '<footer class="report-footer"><span>Dalış Kayıt Raporu</span><span>Sayfa ' + pageNumber + '</span></footer>';
    }

    function chunkItems(items, size) {
        const chunks = [];
        for (let index = 0; index < items.length; index += size) {
            chunks.push(items.slice(index, index + size));
        }
        return chunks;
    }

    function initBackup() {
        const data = getData();
        const approvedCount = data.dives.filter(isApproved).length;
        const pendingCount = data.dives.filter(isPending).length;
        setText('backupSummary', data.students.length + ' öğrenci, ' + data.staff.length + ' staff kullanıcısı, ' + data.admins.length + ' yönetici, ' + approvedCount + ' onaylı dalış ve ' + pendingCount + ' onay bekleyen dalış hazır.');

        document.getElementById('downloadJson').addEventListener('click', function () {
            downloadFile('dalis-sistemi-yedek.json', 'application/json;charset=utf-8', JSON.stringify(getData(), null, 2));
        });

        document.getElementById('downloadAllCsv').addEventListener('click', function () {
            const current = getData();
            const rows = [['type', 'id', 'identifier', 'name', 'date', 'location', 'duration_minutes', 'max_depth', 'approval_status']];
            current.students.forEach(function (student) {
                rows.push(['student', student.id, student.student_no, fullName(student), '', '', '', '', '']);
            });
            current.staff.forEach(function (staff) {
                rows.push(['staff', staff.id, staff.staff_no, fullName(staff), '', '', '', '', '']);
            });
            current.admins.forEach(function (admin) {
                rows.push(['admin', admin.id, admin.admin_no, fullName(admin), '', '', '', '', '']);
            });
            current.dives.forEach(function (dive) {
                const student = current.students.find(function (item) { return item.id === dive.student_id; });
                rows.push(['dive', dive.id, student ? student.student_no : '', student ? fullName(student) : '', dive.dive_date, dive.location, dive.duration_minutes, dive.max_depth, dive.approval_status]);
            });
            downloadCsv('tum-veriler.csv', rows);
        });

        document.getElementById('downloadStudentsCsv').addEventListener('click', function () {
            const rows = [['student_no', 'tc_no', 'name', 'surname', 'program', 'phone', 'email']];
            getData().students.forEach(function (student) {
                rows.push([student.student_no, displayTcNo(student), student.name, student.surname, student.program, student.phone, student.email]);
            });
            downloadCsv('ogrenci-listesi.csv', rows);
        });

        document.getElementById('downloadDivesCsv').addEventListener('click', function () {
            const current = getData();
            const rows = [['student_no', 'name', 'dive_date', 'location', 'dive_type', 'purpose', 'duration_minutes', 'max_depth', 'supervisor_name', 'approval_status', 'approved_at', 'notes']];
            current.dives.forEach(function (dive) {
                const student = current.students.find(function (item) { return item.id === dive.student_id; });
                rows.push([student ? student.student_no : '', student ? fullName(student) : '', dive.dive_date, dive.location, dive.dive_type, dive.purpose, dive.duration_minutes, dive.max_depth, dive.supervisor_name, dive.approval_status, dive.approved_at, dive.notes]);
            });
            downloadCsv('dalis-kayitlari.csv', rows);
        });
    }

    function getActiveStudent(data) {
        if (!data.students.length) {
            window.location.href = 'student-login.html';
            throw new Error('No active student');
        }
        const activeId = Number(localStorage.getItem(ACTIVE_STUDENT_KEY)) || data.students[0].id;
        const student = data.students.find(function (item) {
            return item.id === activeId;
        }) || data.students[0];
        localStorage.setItem(ACTIVE_STUDENT_KEY, String(student.id));
        return student;
    }

    function getActiveStaff(data) {
        if (!data.staff.length) {
            window.location.href = 'staff-login.html';
            throw new Error('No active staff');
        }
        const activeId = Number(localStorage.getItem(ACTIVE_STAFF_KEY)) || data.staff[0].id;
        const staff = data.staff.find(function (item) {
            return item.id === activeId;
        }) || data.staff[0];
        localStorage.setItem(ACTIVE_STAFF_KEY, String(staff.id));
        return staff;
    }

    function getActiveAdmin(data) {
        const activeId = Number(localStorage.getItem(ACTIVE_ADMIN_KEY)) || data.admins[0].id;
        const admin = data.admins.find(function (item) {
            return item.id === activeId;
        }) || data.admins[0];
        localStorage.setItem(ACTIVE_ADMIN_KEY, String(admin.id));
        return admin;
    }

    function getStudentDives(data, studentId) {
        return sortedDives(data.dives.filter(function (dive) {
            return dive.student_id === studentId;
        }));
    }

    function isApproved(dive) {
        return (dive.approval_status || 'approved') === 'approved';
    }

    function isAccountApproved(account) {
        return account && (account.role_name === 'admin' || (account.approval_status || 'approved') === 'approved');
    }

    function isIsteEmail(email) {
        return /^[^\s@]+@iste\.edu\.tr$/i.test(String(email || '').trim());
    }

    async function passwordMatches(password, account) {
        if (account.password_hash) {
            return await digestPassword(String(password || '')) === account.password_hash;
        }

        return account.password === String(password || '');
    }

    async function digestPassword(password) {
        const bytes = new TextEncoder().encode('istedalis|' + password);
        const digest = await crypto.subtle.digest('SHA-256', bytes);
        return Array.from(new Uint8Array(digest)).map(function (byte) {
            return byte.toString(16).padStart(2, '0');
        }).join('');
    }

    function accountStatusBadge(account) {
        return '<span class="status-badge ' + escapeHtml(account.approval_status || 'approved') + '">' +
            escapeHtml(isAccountApproved(account) ? 'Onaylandı' : 'Onay bekliyor') +
        '</span>';
    }

    function isPending(dive) {
        return dive.approval_status === 'pending';
    }

    function statusText(dive) {
        if (isPending(dive)) return 'Onay bekliyor';
        if (isApproved(dive)) return 'Onaylandı';
        return 'Reddedildi';
    }

    function statusBadge(dive) {
        const status = dive.approval_status || 'approved';
        return '<span class="status-badge ' + escapeHtml(status) + '">' + escapeHtml(statusText(dive)) + '</span>';
    }

    function rejectionNote(dive) {
        if (dive.approval_status !== 'rejected' || !dive.rejection_reason) return '';
        return '<small class="status-note">' + escapeHtml(dive.rejection_reason) + '</small>';
    }

    function sortedDives(dives) {
        return dives.slice().sort(function (a, b) {
            return b.dive_date.localeCompare(a.dive_date) || b.id - a.id;
        });
    }

    function buildStats(dives) {
        const duration = dives.reduce(function (sum, dive) {
            return sum + Number(dive.duration_minutes || 0);
        }, 0);
        const maxDepth = dives.reduce(function (max, dive) {
            return Math.max(max, Number(dive.max_depth || 0));
        }, 0);

        return {
            count: dives.length,
            duration: duration,
            maxDepth: maxDepth,
            lastDate: dives[0] ? dives[0].dive_date : null
        };
    }

    function readReportIds() {
        try {
            return JSON.parse(localStorage.getItem(REPORT_IDS_KEY) || '[]').map(Number);
        } catch (error) {
            return [];
        }
    }

    function nextId(items) {
        return items.reduce(function (max, item) {
            return Math.max(max, Number(item.id));
        }, 0) + 1;
    }

    function readString(formData, key) {
        return String(formData.get(key) || '').trim();
    }

    function readNumber(formData, key) {
        const value = String(formData.get(key) || '').trim();
        return value === '' ? '' : Number(value);
    }

    function fullName(person) {
        return [person.name, person.surname].filter(Boolean).join(' ');
    }

    function displayTcNo(student) {
        return student.tc_no || '-';
    }

    function formatDate(value) {
        if (!value) return '-';
        return new Date(value + 'T00:00:00').toLocaleDateString('tr-TR');
    }

    function formatNumber(value) {
        if (value === '' || value === null || value === undefined) return '-';
        return Number(value).toLocaleString('tr-TR', { maximumFractionDigits: 2 });
    }

    function normalize(value) {
        return String(value || '').toLocaleLowerCase('tr-TR').trim();
    }

    function setText(id, value) {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    }

    function showMessage(element, text, success) {
        element.textContent = text;
        element.classList.toggle('success', Boolean(success));
    }

    function summaryLine(label, value) {
        return '<div class="summary-line"><strong>' + escapeHtml(label) + '</strong><span>' + escapeHtml(value || '-') + '</span></div>';
    }

    function detailRow(labelA, valueA, labelB, valueB) {
        return '<tr><th>' + escapeHtml(labelA) + '</th><td>' + escapeHtml(valueA || '-') + '</td><th>' + escapeHtml(labelB) + '</th><td>' + escapeHtml(valueB || '-') + '</td></tr>';
    }

    function escapeHtml(value) {
        return String(value === undefined || value === null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function downloadCsv(filename, rows) {
        const csv = rows.map(function (row) {
            return row.map(csvCell).join(',');
        }).join('\n');
        downloadFile(filename, 'text/csv;charset=utf-8', '\uFEFF' + csv);
    }

    function csvCell(value) {
        const text = String(value === undefined || value === null ? '' : value);
        return '"' + text.replace(/"/g, '""') + '"';
    }

    function downloadFile(filename, type, content) {
        const blob = new Blob([content], { type: type });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
    }
})();
