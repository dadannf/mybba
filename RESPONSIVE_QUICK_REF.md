# 📱 Responsive Quick Reference

## 🚀 Quick Setup (Copy-Paste)

### **Minimal Template:**
```php
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <?php include __DIR__ . '/../includes/responsive_head.php'; ?>
    <title>Page Title</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="/css/dashboard-responsive.css">
</head>
<body class="has-sidebar">
    <!-- Your content here -->
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php include __DIR__ . '/../includes/responsive_scripts.php'; ?>
    <?php include __DIR__ . '/../includes/responsive_manager.php'; ?>
</body>
</html>
```

---

## 📏 Breakpoints

| Code | Size | Device |
|------|------|--------|
| `col-12` | All | Mobile |
| `col-sm-6` | ≥576px | Landscape |
| `col-md-4` | ≥768px | Tablet |
| `col-lg-3` | ≥992px | Desktop |
| `col-xl-2` | ≥1200px | Wide |

---

## 🎨 Common Patterns

### **Responsive Cards:**
```html
<div class="row g-3">
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card">...</div>
    </div>
</div>
```

### **Responsive Table:**
```html
<!-- Auto-wrapped by responsive_manager.php -->
<table class="table table-striped">
    <thead>...</thead>
    <tbody>...</tbody>
</table>
```

### **Responsive Form:**
```html
<div class="row">
    <div class="col-md-6 col-12 mb-3">
        <label class="form-label">Field 1</label>
        <input type="text" class="form-control">
    </div>
    <div class="col-md-6 col-12 mb-3">
        <label class="form-label">Field 2</label>
        <input type="text" class="form-control">
    </div>
</div>
```

### **Responsive Buttons:**
```html
<!-- Will stack vertical on mobile -->
<div class="d-flex gap-2">
    <button class="btn btn-primary">Save</button>
    <button class="btn btn-secondary">Cancel</button>
</div>
```

---

## 🔧 Utility Classes

### **Hide/Show:**
```html
<div class="hide-xs">Hide on mobile</div>
<div class="show-xs-only">Show only on mobile</div>
<div class="d-none d-md-block">Hide on mobile, show tablet+</div>
```

### **Text Truncate:**
```html
<p class="text-truncate-mobile">Long text...</p>
<p class="text-truncate-mobile-2">Max 2 lines</p>
```

### **Spacing:**
```html
<div class="p-3 p-md-4 p-lg-5">Responsive padding</div>
<div class="mb-2 mb-md-3 mb-lg-4">Responsive margin</div>
```

---

## 📱 Testing

### **Chrome DevTools:**
1. F12 → Toggle Device Toolbar (Ctrl+Shift+M)
2. Select device or set custom width
3. Test: 375px, 768px, 1024px, 1920px

### **Test Checklist:**
- [ ] Sidebar toggle di mobile
- [ ] Table scroll horizontal
- [ ] Form stack vertical
- [ ] Buttons stack vertical
- [ ] Modal responsive
- [ ] Images tidak overflow
- [ ] No horizontal scroll

---

## 🐛 Common Issues

### **1. Sidebar tidak muncul di mobile:**
```php
<!-- Pastikan ada -->
<?php include __DIR__ . '/../includes/responsive_manager.php'; ?>
```

### **2. Table overflow:**
```html
<!-- Wrap manual -->
<div class="table-responsive">
    <table class="table">...</table>
</div>
```

### **3. Horizontal scroll:**
```css
/* Add to page CSS */
body { overflow-x: hidden; }
```

### **4. Buttons tidak stack:**
```html
<!-- Use d-flex gap-2 -->
<div class="d-flex gap-2">
    <button class="btn btn-primary">...</button>
</div>
```

---

## 📝 Checklist Halaman Baru

- [ ] Include `responsive_head.php` di `<head>`
- [ ] Include `responsive_scripts.php` sebelum `</body>`
- [ ] Include `responsive_manager.php` sebelum `</body>`
- [ ] Include `dashboard-responsive.css`
- [ ] Use Bootstrap grid (`col-md-6 col-12`)
- [ ] Test di 375px, 768px, 1920px
- [ ] Check no horizontal scroll
- [ ] Check sidebar toggle works
- [ ] Check table scrolls properly
- [ ] Check form fields stack

---

## 🎯 Best Practices

✅ **DO:**
- Use `col-md-6 col-12` pattern
- Use `d-flex gap-2` for buttons
- Use `mb-3` for form groups
- Test on real devices
- Use `rem`/`em` units

❌ **DON'T:**
- Don't use fixed px widths
- Don't forget viewport meta
- Don't disable zoom
- Don't use `!important`
- Don't skip testing

---

## 🔗 Links

- 📖 [Full Guide](../RESPONSIVE_GUIDE.md)
- 🧪 [Test Page](../public/test/responsive_test.php)
- ✅ [Complete Status](../RESPONSIVE_COMPLETE.md)

---

**Quick Copy-Paste Includes:**

```php
<?php include __DIR__ . '/../includes/responsive_head.php'; ?>
<?php include __DIR__ . '/../includes/responsive_scripts.php'; ?>
<?php include __DIR__ . '/../includes/responsive_manager.php'; ?>
```
