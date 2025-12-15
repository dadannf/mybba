# 🧪 Responsive Test Page

## 📍 Akses

**URL:** `http://localhost/test/responsive_test.php`

---

## 🎯 Fitur Test Page

### **1. Visual Viewport Info**
- Real-time display ukuran layar (width x height)
- Auto-detect device type (Mobile/Tablet/Desktop)
- Update otomatis saat resize

### **2. Responsive Sidebar**
- ✅ Slide-in di mobile
- ✅ Fixed di desktop
- ✅ Overlay backdrop
- ✅ Auto-close saat klik outside

### **3. Responsive Cards**
- ✅ 4 kolom di desktop (xl)
- ✅ 3 kolom di tablet landscape (lg)
- ✅ 2 kolom di tablet portrait (md)
- ✅ 1 kolom di mobile (xs/sm)

### **4. Responsive Table**
- ✅ Horizontal scroll di mobile
- ✅ Scroll indicator
- ✅ Stack action buttons
- ✅ Font size adaptation

### **5. Responsive Form**
- ✅ 2 kolom di desktop
- ✅ 1 kolom di mobile
- ✅ Full-width inputs
- ✅ Stack buttons vertical

### **6. Responsive Modal**
- ✅ Fullscreen di mobile (< 576px)
- ✅ Centered di desktop
- ✅ Stack footer buttons

---

## 🧪 Cara Testing

### **1. Chrome DevTools:**
```
1. Buka http://localhost/test/responsive_test.php
2. Press F12
3. Click "Toggle Device Toolbar" (Ctrl+Shift+M)
4. Pilih device atau set custom width
```

### **2. Recommended Test Widths:**
- **320px** - iPhone SE (smallest)
- **375px** - iPhone 12/13
- **390px** - iPhone 14
- **768px** - iPad Mini
- **1024px** - iPad Pro
- **1366px** - Laptop
- **1920px** - Desktop

### **3. Test Checklist:**
- [ ] Sidebar toggle di mobile (< 768px)
- [ ] Sidebar fixed di desktop (>= 768px)
- [ ] Table scroll horizontal di mobile
- [ ] Cards stack 1 kolom di mobile
- [ ] Form fields stack vertical di mobile
- [ ] Buttons stack vertical di mobile
- [ ] Modal fullscreen di mobile
- [ ] No horizontal page scroll
- [ ] Viewport info updates
- [ ] All elements visible

---

## 📱 Expected Behavior

### **Mobile (< 576px):**
- Sidebar hidden, toggle button visible
- All cards 1 column
- Table scrolls horizontally
- Form fields full width
- Buttons stack vertical
- Modal fullscreen

### **Tablet (768px - 991px):**
- Sidebar visible (240px)
- Cards 2-3 columns
- Table full width
- Form 2 columns
- Buttons inline

### **Desktop (>= 1200px):**
- Sidebar 260px
- Cards 4 columns
- All elements spacious
- Full layout visible

---

## 🎨 Visual Guide

### **Viewport Info Box:**
Lokasi: Fixed bottom-right corner
```
Width: 375px
Height: 667px
Type: Mobile (xs)
```

### **Sidebar Toggle:**
- **Mobile:** Hamburger icon (☰) top-left
- **Desktop:** Hidden (sidebar always visible)

### **Table Scroll Indicator:**
- Muncul di mobile saat table > viewport width
- Label: "← Scroll →"
- Auto-update saat scroll

---

## 🔧 Customization

Edit file `responsive_test.php` untuk:
- Tambah section baru
- Modify test data
- Change colors/styles
- Add more examples

---

## 📝 Notes

- Test page menggunakan **dummy data**
- Semua fitur **non-functional** (demo only)
- Focus pada **visual responsiveness**
- Safe untuk testing di semua browser

---

## 🐛 Known Issues

**None** - All features working as expected!

---

## 🔗 Related

- [Full Guide](../../RESPONSIVE_GUIDE.md)
- [Quick Reference](../../RESPONSIVE_QUICK_REF.md)
- [Complete Status](../../RESPONSIVE_COMPLETE.md)

---

**Happy Testing! 🎉**
