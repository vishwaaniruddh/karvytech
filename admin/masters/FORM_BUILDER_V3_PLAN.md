# Form Builder V3 - Drag & Drop Implementation Plan

## Overview
Enhance form-maker-v2.php with drag-and-drop functionality for intuitive form building.

## Key Features to Add

### 1. Drag & Drop Library
- **Library**: SortableJS (https://sortablejs.github.io/Sortable/)
- **CDN**: `<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>`

### 2. Drag & Drop Capabilities

#### A. Section Reordering
- Drag sections up/down to reorder
- Visual feedback during drag
- Auto-save order on drop

#### B. Subsection Reordering
- Drag subsections within parent section
- Drag subsections between sections
- Visual nesting indicators

#### C. Field Reordering
- Drag fields within section
- Drag fields within subsection
- Drag fields between sections/subsections
- Visual drop zones

#### D. Field Type Palette
- Draggable field type buttons in sidebar
- Drag from palette to add new field
- Clone on drag (palette item stays)

### 3. Visual Enhancements

#### Drag States
```css
.sortable-ghost {
    opacity: 0.4;
    background: #e3f2fd;
}

.sortable-drag {
    opacity: 0.8;
    transform: rotate(2deg);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.sortable-chosen {
    background: #f5f5f5;
}

.drop-zone-active {
    border: 2px dashed #2196f3;
    background: #e3f2fd;
}
```

#### Drag Handles
- Add grip icon to each draggable item
- Only drag when handle is grabbed
- Prevents accidental drags

### 4. Implementation Steps

#### Step 1: Update HTML Structure
```html
<!-- Add drag handle to sections -->
<div class="section-item" data-section-id="...">
    <div class="drag-handle">
        <svg><!-- grip icon --></svg>
    </div>
    <div class="section-content">...</div>
</div>
```

#### Step 2: Initialize Sortable
```javascript
// Sections sortable
new Sortable(document.getElementById('sections-container'), {
    animation: 150,
    handle: '.drag-handle',
    ghostClass: 'sortable-ghost',
    onEnd: function(evt) {
        // Update section order in Vue
        app.reorderSections(evt.oldIndex, evt.newIndex);
    }
});

// Fields sortable
new Sortable(document.getElementById('fields-container'), {
    group: 'fields',
    animation: 150,
    handle: '.drag-handle',
    onEnd: function(evt) {
        // Update field order
    }
});
```

#### Step 3: Field Palette
```html
<div class="field-palette">
    <div class="palette-item" data-field-type="text">
        <svg><!-- icon --></svg>
        Text Input
    </div>
    <!-- More field types -->
</div>
```

```javascript
new Sortable(document.getElementById('field-palette'), {
    group: {
        name: 'fields',
        pull: 'clone',
        put: false
    },
    sort: false,
    onEnd: function(evt) {
        // Add new field of this type
    }
});
```

### 5. Additional Features

#### A. Keyboard Shortcuts
- `Ctrl+S`: Save form
- `Ctrl+P`: Preview
- `Delete`: Remove selected item
- `Ctrl+D`: Duplicate selected item
- `Ctrl+Z`: Undo
- `Ctrl+Y`: Redo

#### B. Undo/Redo System
```javascript
undoStack: [],
redoStack: [],
saveState() {
    this.undoStack.push(JSON.parse(JSON.stringify(this.form)));
    this.redoStack = [];
},
undo() {
    if (this.undoStack.length > 0) {
        this.redoStack.push(JSON.parse(JSON.stringify(this.form)));
        this.form = this.undoStack.pop();
    }
}
```

#### C. Field Duplication
- Right-click context menu
- Duplicate button on hover
- Preserves all settings

#### D. Bulk Operations
- Select multiple fields (Ctrl+Click)
- Delete multiple
- Move multiple
- Change properties of multiple

#### E. Templates
- Save form as template
- Load from template
- Template library

#### F. Field Search
- Search fields by label
- Filter by type
- Quick navigation

### 6. Mobile Responsiveness
- Touch-friendly drag handles
- Larger touch targets
- Swipe gestures
- Responsive layout

### 7. Performance Optimizations
- Virtual scrolling for large forms
- Debounced auto-save
- Lazy load field configurations
- Minimize re-renders

## File Structure
```
admin/masters/
├── form-maker-v2.php (current)
├── form-maker-v3.php (new with drag-drop)
├── form-maker-v3.js (extracted Vue logic)
└── form-maker-v3.css (extracted styles)
```

## Migration Path
1. Keep v2 as stable version
2. Build v3 alongside
3. Add "Try V3 Beta" button in v2
4. Collect feedback
5. Make v3 default when stable
6. Keep v2 as "Classic Mode"

## Testing Checklist
- [ ] Drag sections
- [ ] Drag subsections
- [ ] Drag fields
- [ ] Drag from palette
- [ ] Cross-section drag
- [ ] Undo/Redo
- [ ] Keyboard shortcuts
- [ ] Mobile touch
- [ ] Save/Load
- [ ] Preview
- [ ] Export

## Timeline
- Phase 1 (Week 1): Basic drag-drop for sections
- Phase 2 (Week 2): Field drag-drop and palette
- Phase 3 (Week 3): Undo/redo and shortcuts
- Phase 4 (Week 4): Polish and testing
