<script type="text/javascript">
    var gk_isXlsx = false;
    var gk_xlsxFileLookup = {};
    var gk_fileData = {};
    function filledCell(cell) {
        return cell !== '' && cell != null;
    }
    function loadFileData(filename) {
        if (gk_isXlsx && gk_xlsxFileLookup[filename]) {
            try {
                var workbook = XLSX.read(gk_fileData[filename], { type: 'base64' });
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];

                // Convert sheet to JSON to filter blank rows
                var jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, blankrows: false, defval: '' });
                // Filter out blank rows (rows where all cells are empty, null, or undefined)
                var filteredData = jsonData.filter(row => row.some(filledCell));

                // Heuristic to find the header row by ignoring rows with fewer filled cells than the next row
                var headerRowIndex = filteredData.findIndex((row, index) =>
                    row.filter(filledCell).length >= filteredData[index + 1]?.filter(filledCell).length
                );
                // Fallback
                if (headerRowIndex === -1 || headerRowIndex > 25) {
                    headerRowIndex = 0;
                }

                // Convert filtered JSON back to CSV
                var csv = XLSX.utils.aoa_to_sheet(filteredData.slice(headerRowIndex)); // Create a new sheet from filtered array of arrays
                csv = XLSX.utils.sheet_to_csv(csv, { header: 1 });
                return csv;
            } catch (e) {
                console.error(e);
                return "";
            }
        }
        return gk_fileData[filename] || "";
    }
</script><!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>یادآور حرفه‌ای با اعلان</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Vazir', Arial, sans-serif;
            direction: rtl;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 1rem;
            transition: background 0.3s;
        }
        .light {
            background: linear-gradient(135deg, #a1c4fd, #c2e9fb);
        }
        .dark {
            background: linear-gradient(135deg, #2c3e50, #4b6cb7);
            color: white;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            max-width: 600px;
            width: 100%;
        }
        .dark .container {
            background: rgba(0, 0, 0, 0.7);
        }
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .reminder-item {
            animation: slideIn 0.3s ease-out;
        }
        .pulse:hover {
            transform: scale(1.02);
            transition: transform 0.2s;
        }
        #notificationStatus {
            transition: opacity 0.3s;
        }
    </style>
</head>
<body class="light">
<div class="container">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-200">یادآور حرفه‌ای با اعلان</h2>
        <button onclick="toggleTheme()" class="p-2 rounded-full bg-gray-200 dark:bg-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>
    </div>
    <div id="notificationStatus" class="mb-4 p-3 rounded-lg bg-yellow-100 text-yellow-800 hidden">
        برای فعال‌سازی اعلان‌ها، لطفاً اجازه دهید.
        <button onclick="requestNotificationPermission()" class="underline">اجازه دادن</button>
    </div>
    <div class="space-y-4">
        <input type="text" id="reminderText" placeholder="متن یادآور" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <input type="datetime-local" id="reminderTime" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <input type="text" id="reminderTags" placeholder="تگ‌ها (با کاما جدا کنید)" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select id="reminderSound" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="beep1">زنگ ۱ (کوتاه)</option>
            <option value="beep2">زنگ ۲ (بلند)</option>
            <option value="beep3">زنگ ۳ (ملایم)</option>
        </select>
        <button onclick="previewSound()" class="w-full bg-green-500 text-white p-3 rounded-lg hover:bg-green-600 transition">پیش‌نمایش زنگ</button>
        <select id="reminderRepeat" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="none">بدون تکرار</option>
            <option value="daily">روزانه</option>
            <option value="weekly">هفتگی</option>
            <option value="monthly">ماهانه</option>
        </select>
        <button onclick="setReminder()" class="w-full bg-indigo-600 text-white p-3 rounded-lg hover:bg-indigo-700 transition">تنظیم یادآور</button>
    </div>
    <div class="mt-6 flex space-x-2">
        <input type="text" id="searchInput" placeholder="جستجو بر اساس متن یا تگ" class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button onclick="searchReminders()" class="bg-blue-500 text-white p-3 rounded-lg hover:bg-blue-600">جستجو</button>
    </div>
    <div class="mt-4 flex space-x-2">
        <button onclick="exportReminders()" class="bg-gray-500 text-white p-3 rounded-lg hover:bg-gray-600">خروجی گرفتن</button>
        <input type="file" id="importFile" accept=".json" class="p-3">
        <button onclick="importReminders()" class="bg-gray-500 text-white p-3 rounded-lg hover:bg-gray-600">وارد کردن</button>
    </div>
    <div id="reminders" class="mt-6 space-y-3"></div>
</div>

<audio id="beep1" src="https://www.soundjay.com/buttons/beep-01a.mp3"></audio>
<audio id="beep2" src="https://www.soundjay.com/buttons/beep-07.mp3"></audio>
<audio id="beep3" src="https://www.soundjay.com/buttons/beep-08b.mp3"></audio>

<script>
    // Check and request notification permission
    function requestNotificationPermission() {
        Notification.requestPermission().then(permission => {
            updateNotificationStatus(permission);
        });
    }

    function updateNotificationStatus(permission) {
        const statusDiv = document.getElementById('notificationStatus');
        if (permission === 'granted') {
            statusDiv.classList.add('hidden');
        } else {
            statusDiv.classList.remove('hidden');
        }
    }

    // Initialize notification status
    updateNotificationStatus(Notification.permission);

    let reminders = JSON.parse(localStorage.getItem('reminders')) || [];
    let theme = localStorage.getItem('theme') || 'light';
    document.body.className = theme;

    function toggleTheme() {
        theme = theme === 'light' ? 'dark' : 'light';
        document.body.className = theme;
        localStorage.setItem('theme', theme);
    }

    function loadReminders(filter = '') {
        const reminderDiv = document.getElementById('reminders');
        reminderDiv.innerHTML = '';
        reminders
            .filter(rem =>
                rem.text.includes(filter) ||
                (rem.tags && rem.tags.some(tag => tag.includes(filter)))
            )
            .forEach((rem, index) => addReminderToDOM(rem, index));
    }

    function addReminderToDOM(rem, index) {
        const reminderDiv = document.getElementById('reminders');
        const reminderItem = document.createElement('div');
        reminderItem.className = 'reminder-item bg-gray-100 dark:bg-gray-800 p-3 rounded-lg flex justify-between items-center pulse';
        reminderItem.innerHTML = `
                <div>
                    <span class="font-bold">${rem.text}</span> - ${new Date(rem.time).toLocaleString('fa-IR')}
                    <span class="text-sm text-gray-500">(${rem.sound}, ${rem.repeat})</span>
                    <div class="text-sm text-indigo-500">${rem.tags ? rem.tags.join(', ') : ''}</div>
                </div>
                <div>
                    <button onclick="editReminder(${index})" class="text-blue-500 hover:text-blue-700 mr-2">ویرایش</button>
                    <button onclick="deleteReminder(${index})" class="text-red-500 hover:text-red-700">حذف</button>
                </div>
            `;
        reminderDiv.appendChild(reminderItem);
    }

    function setReminder() {
        const text = document.getElementById('reminderText').value;
        const time = document.getElementById('reminderTime').value;
        const tags = document.getElementById('reminderTags').value.split(',').map(t => t.trim()).filter(t => t);
        const sound = document.getElementById('reminderSound').value;
        const repeat = document.getElementById('reminderRepeat').value;

        if (!text || !time) {
            alert('لطفاً متن و زمان یادآور را وارد کنید!');
            return;
        }

        const reminderTime = new Date(time);
        const now = new Date();

        if (reminderTime <= now) {
            alert('زمان یادآور باید در آینده باشد!');
            return;
        }

        const reminder = { text, time, tags, sound, repeat };
        reminders.push(reminder);
        localStorage.setItem('reminders', JSON.stringify(reminders));
        addReminderToDOM(reminder, reminders.length - 1);

        scheduleReminder(reminder, reminders.length - 1);
        document.getElementById('reminderText').value = '';
        document.getElementById('reminderTime').value = '';
        document.getElementById('reminderTags').value = '';
    }

    function scheduleReminder(rem, index) {
        const reminderTime = new Date(rem.time);
        const now = new Date();
        const timeDiff = reminderTime - now;

        setTimeout(() => {
            const audio = document.getElementById(rem.sound);
            audio.play().catch(e => console.log('Error playing sound:', e));
            if ('vibrate' in navigator) {
                navigator.vibrate([200, 100, 200, 100, 200]);
            }
            if (Notification.permission === 'granted') {
                new Notification('یادآور حرفه‌ای', {
                    body: rem.text,
                    icon: 'https://www.pngall.com/wp-content/uploads/2016/04/Alarm-Clock-PNG.png',
                    tag: `reminder-${index}`,
                    actions: [
                        { action: 'snooze', title: 'چرت زدن (۵ دقیقه)' },
                        { action: 'dismiss', title: 'بستن' }
                    ]
                }).onclick = () => window.focus();
            } else {
                alert(`یادآور: ${rem.text}`);
            }

            if (rem.repeat !== 'none') {
                let newTime;
                if (rem.repeat === 'daily') {
                    newTime = new Date(reminderTime.getTime() + 24 * 60 * 60 * 1000);
                } else if (rem.repeat === 'weekly') {
                    newTime = new Date(reminderTime.getTime() + 7 * 24 * 60 * 60 * 1000);
                } else if (rem.repeat === 'monthly') {
                    newTime = new Date(reminderTime.setMonth(reminderTime.getMonth() + 1));
                }
                rem.time = newTime.toISOString().slice(0, 16);
                localStorage.setItem('reminders', JSON.stringify(reminders));
                scheduleReminder(rem, index);
            } else {
                deleteReminder(index);
            }
        }, timeDiff);
    }

    function deleteReminder(index) {
        reminders.splice(index, 1);
        localStorage.setItem('reminders', JSON.stringify(reminders));
        loadReminders();
    }

    function editReminder(index) {
        const rem = reminders[index];
        document.getElementById('reminderText').value = rem.text;
        document.getElementById('reminderTime').value = rem.time.slice(0, 16);
        document.getElementById('reminderTags').value = rem.tags ? rem.tags.join(', ') : '';
        document.getElementById('reminderSound').value = rem.sound;
        document.getElementById('reminderRepeat').value = rem.repeat;
        deleteReminder(index);
    }

    function previewSound() {
        const sound = document.getElementById('reminderSound').value;
        const audio = document.getElementById(sound);
        audio.play().catch(e => console.log('Error playing sound:', e));
    }

    function searchReminders() {
        const filter = document.getElementById('searchInput').value;
        loadReminders(filter);
    }

    function exportReminders() {
        const dataStr = JSON.stringify(reminders);
        const blob = new Blob([dataStr], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'reminders.json';
        a.click();
        URL.revokeObjectURL(url);
    }

    function importReminders() {
        const fileInput = document.getElementById('importFile');
        const file = fileInput.files[0];
        if (!file) {
            alert('لطفاً فایل را انتخاب کنید!');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                reminders = JSON.parse(e.target.result);
                localStorage.setItem('reminders', JSON.stringify(reminders));
                loadReminders();
                reminders.forEach((rem, index) => scheduleReminder(rem, index));
                alert('یادآورها با موفقیت وارد شدند!');
            } catch (e) {
                alert('خطا در بارگذاری فایل!');
            }
        };
        reader.readAsText(file);
    }

    // Load existing reminders and schedule them
    loadReminders();
    reminders.forEach((rem, index) => scheduleReminder(rem, index));
</script>
</body>
</html>