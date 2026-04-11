<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/DynamicSurvey.php';

$id = $_GET['id'] ?? null;
$customer_id = $_GET['customer_id'] ?? null;
$type = $_GET['type'] ?? 'survey';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Builder V3 - Drag & Drop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); }
        .sidebar-scroll { max-height: calc(100vh - 200px); overflow-y: auto; }
        .active-item { border-color: #2563eb; ring: 2px; ring-color: #3b82f6; }
        [v-cloak] { display: none; }
        
        /* Drag & Drop Styles */
        .sortable-ghost {
            opacity: 0.4;
            background: #e3f2fd !important;
            border: 2px dashed #2196f3 !important;
        }
        
        .sortable-drag {
            opacity: 0.9;
            transform: rotate(1deg);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
            cursor: grabbing !important;
        }
        
        .sortable-chosen {
            background: #f5f5f5;
        }
        
        .drag-handle {
            cursor: grab !important;
            opacity: 1;
            transition: all 0.2s;
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
        
        .drag-handle:hover {
            opacity: 1;
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .drag-handle:active {
            cursor: grabbing !important;
            transform: scale(0.95);
        }
        
        .palette-item {
            cursor: grab;
            transition: all 0.2s;
        }
        
        .palette-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .palette-item:active {
            cursor: grabbing;
        }
        
        .shortcut-badge {
            display: inline-block;
            padding: 2px 6px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            color: #6b7280;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 overflow-hidden">

<div id="app" class="h-screen flex flex-col" v-cloak>
    <!-- Top Header -->
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
        <div class="flex items-center gap-4">
            <a href="form-maker.php" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <input v-model="form.title" type="text" class="text-xl font-bold bg-transparent border-none focus:ring-0 w-80 p-0" placeholder="Untitled Form">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Dynamic Form Builder V3 - Drag & Drop Edition</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showRevisions" v-if="form.id" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                History
            </button>
            <button @click="previewForm" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                Preview
            </button>
            <button @click="saveForm" :disabled="saving" class="px-6 py-2 bg-blue-600 rounded-lg text-sm font-bold text-white hover:bg-blue-700 shadow-lg shadow-blue-200 disabled:opacity-50 transition-all flex items-center gap-2">
                <span v-if="saving">Saving...</span>
                <span v-else>Save Changes</span>
            </button>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">
        <!-- Left Sidebar: Tools & Outline -->
        <aside class="w-72 bg-white border-r border-gray-200 flex flex-col">
            <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-purple-50">
                <div class="flex items-center gap-2 text-blue-600 mb-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"></path></svg>
                    <span class="text-xs font-bold">Drag & Drop Enabled!</span>
                </div>
                <p class="text-xs text-gray-600 mb-2">
                    🎯 Drag sections to reorder<br>
                    🎨 Drag field types to add<br>
                    ⌨️ <span class="shortcut-badge">Ctrl+S</span> to save
                </p>
            </div>
            <div class="sidebar-scroll flex-1 p-4 space-y-6">
                <!-- Customer Selection -->
                <div class="pb-4 border-b border-gray-100">
                    <h3 class="text-xs font-bold text-gray-400 uppercase mb-3">Customer</h3>
                    <select v-model="form.customer_id" @change="showCustomerError = false" class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-blue-500 transition-all" :class="{'border-red-300 ring-2 ring-red-200': showCustomerError}" id="customer">
                        <option value="" selected>-- Select Customer --</option>
                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                            {{ customer.name }}
                        </option>
                    </select>
                    <p v-if="showCustomerError" class="text-xs text-red-500 mt-1.5">Required: Select a customer</p>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase mb-4">Structure</h3>
                    <button @click="addSection" class="w-full py-3 px-4 bg-gray-50 border border-dashed border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition-all flex items-center justify-center gap-2 group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Main Section
                    </button>
                    <button @click="addSubsection" v-if="selectedType === 'section'" class="w-full mt-2 py-2 px-4 bg-purple-50 border border-dashed border-purple-300 rounded-xl text-xs font-semibold text-purple-700 hover:bg-purple-100 hover:border-purple-400 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Subsection Inside
                    </button>
                </div>
                
                <!-- Field Palette -->
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase mb-3">Field Types</h3>
                    <p class="text-[10px] text-gray-500 mb-3">Drag to canvas to add</p>
                    <div id="field-palette" class="grid grid-cols-2 gap-2">
                        <div class="palette-item p-2 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all" data-field-type="text">
                            <div class="text-xs font-semibold text-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                Text
                            </div>
                        </div>
                        <div class="palette-item p-2 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all" data-field-type="number">
                            <div class="text-xs font-semibold text-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                                Number
                            </div>
                        </div>
                        <div class="palette-item p-2 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all" data-field-type="email">
                            <div class="text-xs font-semibold text-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                Email
                            </div>
                        </div>
                        <div class="palette-item p-2 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all" data-field-type="textarea">
                            <div class="text-xs font-semibold text-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                                Textarea
                            </div>
                        </div>
                        <div class="palette-item p-2 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all" data-field-type="select">
                            <div class="text-xs font-semibold text-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                                Select
                            </div>
                        </div>
                        <div class="palette-item p-2 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all" data-field-type="radio">
                            <div class="text-xs font-semibold text-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Radio
                            </div>
                        </div>
                        <div class="palette-item p-2 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all" data-field-type="checkbox">
                            <div class="text-xs font-semibold text-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                Checkbox
                            </div>
                        </div>
                        <div class="palette-item p-2 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all" data-field-type="date">
                            <div class="text-xs font-semibold text-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Date
                            </div>
                        </div>
                        <div class="palette-item p-2 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all" data-field-type="time">
                            <div class="text-xs font-semibold text-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Time
                            </div>
                        </div>
                        <div class="palette-item p-2 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all" data-field-type="datetime">
                            <div class="text-xs font-semibold text-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                DateTime
                            </div>
                        </div>
                        <div class="palette-item p-2 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all" data-field-type="file">
                            <div class="text-xs font-semibold text-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                File
                            </div>
                        </div>
                        <div class="palette-item p-2 bg-white border border-gray-200 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all" data-field-type="customer">
                            <div class="text-xs font-semibold text-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Customer
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="form.sections.length > 0">
                    <h3 class="text-xs font-bold text-gray-400 uppercase mb-3">Outline</h3>
                    <div class="space-y-2">
                        <div v-for="(section, sIndex) in form.sections" :key="sIndex" class="group">
                            <div @click="selectElement('section', sIndex)" :class="{'bg-blue-50 text-blue-700': selectedType === 'section' && selectedIndex === sIndex}" class="p-2 rounded-lg cursor-pointer text-sm font-medium hover:bg-gray-50 transition-colors flex items-center justify-between">
                                <span class="truncate">{{ section.title || 'Untitled Section' }}</span>
                                <span class="text-[10px] bg-gray-200 text-gray-500 px-1.5 rounded">{{ (section.subsections?.length || 0) + section.fields.length }}</span>
                            </div>
                            <!-- Subsections -->
                            <div v-if="section.subsections && section.subsections.length > 0" class="ml-4 mt-1 space-y-1">
                                <div v-for="(subsection, subIndex) in section.subsections" :key="subIndex" @click="selectElement('subsection', sIndex, subIndex)" :class="{'bg-purple-50 text-purple-700': selectedType === 'subsection' && selectedSectionIndex === sIndex && selectedIndex === subIndex}" class="p-2 rounded-lg cursor-pointer text-xs font-medium hover:bg-gray-50 transition-colors flex items-center justify-between">
                                    <span class="truncate flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        {{ subsection.title || 'Untitled Subsection' }}
                                    </span>
                                    <span class="text-[10px] bg-purple-200 text-purple-600 px-1.5 rounded">{{ subsection.fields.length }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="flex-1 overflow-y-auto p-12 bg-gray-50/50">
            <div class="max-w-3xl mx-auto space-y-12 pb-32">
                <!-- Form Header Box -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-200">
                    <input v-model="form.title" class="w-full text-3xl font-extrabold text-gray-900 border-none focus:ring-0 p-0 mb-2" placeholder="Form Title">
                    <textarea v-model="form.description" class="w-full text-gray-500 border-none focus:ring-0 p-0 resize-none h-auto" placeholder="Form Description (optional)" rows="1" @input="autoResize"></textarea>
                </div>

                <!-- Sections Container -->
                <div id="sections-container">
                    <div v-for="(section, sIndex) in form.sections" :key="'section-' + sIndex" class="section-item mb-6" :data-section-index="sIndex">
                        <!-- Section Header -->
                        <div @click="selectElement('section', sIndex)" :class="{'ring-2 ring-blue-500': selectedType === 'section' && selectedIndex === sIndex}" class="bg-white rounded-3xl p-8 shadow-sm border border-gray-200 transition-all group relative">
                            <div class="flex items-start gap-3 mb-4">
                                <!-- Drag Handle -->
                                <div class="drag-handle pt-2 flex-shrink-0" style="background: #fef3c7; padding: 8px; border-radius: 8px;">
                                    <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"></path>
                                    </svg>
                                    <div class="text-[8px] text-gray-600 font-bold mt-1">DRAG</div>
                                </div>
                                <div class="flex-1">
                                    <input v-model="section.title" class="w-full text-xl font-bold text-gray-800 border-none focus:ring-0 p-0 mb-1 bg-transparent" placeholder="Section Title">
                                    <input v-model="section.description" class="w-full text-sm text-gray-500 border-none focus:ring-0 p-0 bg-transparent" placeholder="Section Description">
                                </div>
                                <button @click.stop="removeSection(sIndex)" class="p-2 opacity-0 group-hover:opacity-100 text-red-300 hover:text-red-500 transition-all flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        
                        <div class="border-t border-gray-100 pt-6 space-y-6">
                            <!-- Subsections -->
                            <div v-for="(subsection, subIndex) in section.subsections" :key="'sub-' + subIndex" class="bg-purple-50/50 rounded-2xl p-6 border-2 border-purple-200">
                                <div @click.stop="selectElement('subsection', sIndex, subIndex)" :class="{'ring-2 ring-purple-500': selectedType === 'subsection' && selectedSectionIndex === sIndex && selectedIndex === subIndex}" class="cursor-pointer">
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        <input v-model="subsection.title" class="flex-1 text-lg font-bold text-purple-900 border-none focus:ring-0 p-0 bg-transparent" placeholder="Subsection Title">
                                    </div>
                                    <input v-model="subsection.description" class="w-full text-xs text-purple-700 border-none focus:ring-0 p-0 mb-4 bg-transparent" placeholder="Subsection Description">
                                    
                                    <!-- Fields in Subsection -->
                                    <div class="space-y-3">
                                        <div v-for="(field, fIndex) in subsection.fields" :key="fIndex" @click.stop="selectElement('field', sIndex, fIndex, subIndex)" :class="{'ring-2 ring-blue-500': selectedType === 'field' && selectedSectionIndex === sIndex && selectedSubsectionIndex === subIndex && selectedIndex === fIndex}" class="p-4 bg-white rounded-xl border border-purple-100 hover:border-blue-200 transition-all cursor-pointer relative group">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="text-[9px] font-bold text-blue-500 uppercase">{{ field.field_type }}</span>
                                                        <span v-if="field.field_width === 'half'" class="text-[8px] bg-purple-100 text-purple-600 px-1 py-0.5 rounded font-bold">50%</span>
                                                        <span v-if="field.field_width === 'third'" class="text-[8px] bg-green-100 text-green-600 px-1 py-0.5 rounded font-bold">33%</span>
                                                        <span v-if="field.field_width === 'quarter'" class="text-[8px] bg-orange-100 text-orange-600 px-1 py-0.5 rounded font-bold">25%</span>
                                                        <span v-if="field.is_required" class="text-red-500 font-bold text-xs">*</span>
                                                    </div>
                                                    <input v-model="field.label" class="w-full text-sm font-semibold text-gray-700 bg-transparent border-none focus:ring-0 p-0" placeholder="Question Text">
                                                </div>
                                                <button @click.stop="removeField(sIndex, fIndex, subIndex)" class="p-1 opacity-0 group-hover:opacity-100 text-red-300 hover:text-red-500">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Add Field to Subsection -->
                                        <button @click.stop="addField(sIndex, subIndex)" class="w-full py-3 border border-dashed border-purple-300 rounded-xl text-xs font-bold text-purple-600 hover:border-purple-400 hover:bg-purple-50 transition-all flex items-center justify-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                            Add Question
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Fields in Main Section (not in subsections) -->
                            <div v-for="(field, fIndex) in section.fields" :key="fIndex" @click.stop="selectElement('field', sIndex, fIndex)" :class="{'ring-2 ring-blue-500': selectedType === 'field' && selectedSectionIndex === sIndex && selectedIndex === fIndex && selectedSubsectionIndex === null}" class="p-6 bg-gray-50 rounded-2xl border border-gray-100 hover:border-blue-200 transition-all cursor-pointer relative group">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[10px] font-bold text-blue-500 uppercase tracking-tighter">{{ field.field_type }}</span>
                                            <span v-if="field.field_width === 'half'" class="text-[9px] bg-purple-100 text-purple-600 px-1.5 py-0.5 rounded font-bold">50%</span>
                                            <span v-if="field.field_width === 'third'" class="text-[9px] bg-green-100 text-green-600 px-1.5 py-0.5 rounded font-bold">33%</span>
                                            <span v-if="field.field_width === 'quarter'" class="text-[9px] bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded font-bold">25%</span>
                                            <span v-if="field.is_required" class="text-red-500 font-bold">*</span>
                                        </div>
                                        <input v-model="field.label" class="w-full font-semibold text-gray-700 bg-transparent border-none focus:ring-0 p-0" placeholder="Question Text">
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all">
                                        <button @click.stop="moveField(sIndex, fIndex, -1)" class="p-1 text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                        </button>
                                        <button @click.stop="moveField(sIndex, fIndex, 1)" class="p-1 text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </button>
                                        <button @click.stop="removeField(sIndex, fIndex)" class="p-1 text-red-300 hover:text-red-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-400 border-t border-gray-200/50 pt-2 flex items-center gap-2">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ field.placeholder || 'No placeholder set' }}
                                </div>
                            </div>

                            <!-- Add Field within Section -->
                            <button @click.stop="addField(sIndex)" class="w-full py-4 border-2 border-dashed border-gray-200 rounded-2xl text-sm font-bold text-gray-400 hover:border-blue-400 hover:text-blue-500 hover:bg-blue-50/30 transition-all flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Add Question to this Section
                            </button>
                        </div>
                    </div>
                    </div>
                </div>

                <div v-if="form.sections.length === 0" class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-200">
                    <p class="text-gray-400 font-medium">Your form is empty. Start by adding a section.</p>
                </div>
            </div>
        </main>

        <!-- Right Sidebar: Configuration -->
        <aside class="w-80 bg-white border-l border-gray-200 flex flex-col glass">
            <div class="p-6 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Configuration</h3>
                <p class="text-xs text-gray-500">Customize the selected element</p>
            </div>
            
            <div class="sidebar-scroll flex-1 p-6 space-y-8" v-if="selectedElement">
                <!-- Field Specific Config -->
                <div v-if="selectedType === 'field'" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Type</label>
                        <select v-model="selectedElement.field_type" class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-blue-500 transition-all">
                            <optgroup label="Basic">
                                <option value="text">Short Text</option>
                                <option value="number">Number</option>
                                <option value="email">Email</option>
                                <option value="password">Password</option>
                            </optgroup>
                            <optgroup label="Multi-line">
                                <option value="textarea">Paragraph</option>
                            </optgroup>
                            <optgroup label="Selections">
                                <option value="select">Dropdown</option>
                                <option value="radio">Radio Buttons</option>
                                <option value="checkbox">Checkboxes</option>
                            </optgroup>
                            <optgroup label="Dynamic">
                                <option value="customer">Customer Dropdown</option>
                            </optgroup>
                            <optgroup label="Date & Time">
                                <option value="date">Date</option>
                                <option value="time">Time</option>
                                <option value="datetime">DateTime</option>
                            </optgroup>
                            <optgroup label="Upload">
                                <option value="file">File Upload</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-gray-50 rounded-xl border border-gray-100 hover:bg-gray-100 transition-colors">
                            <input type="checkbox" v-model="selectedElement.is_required" class="w-4 h-4 text-blue-600 rounded">
                            <span class="text-sm font-semibold text-gray-700">Required field</span>
                        </label>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Placeholder</label>
                        <input v-model="selectedElement.placeholder" type="text" class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500" placeholder="Helpful hint...">
                    </div>

                    <div class="space-y-2" v-if="selectedElement.field_type === 'number'">
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-gray-50 rounded-xl border border-gray-100 hover:bg-gray-100 transition-colors">
                            <input type="checkbox" v-model="selectedElement.allow_negative" class="w-4 h-4 text-blue-600 rounded">
                            <span class="text-sm font-semibold text-gray-700">Allow negative numbers</span>
                        </label>
                        <p class="text-xs text-gray-400">When unchecked, only positive numbers (0 and above) are allowed</p>
                    </div>

                    <!-- File Upload Configuration -->
                    <div v-if="selectedElement.field_type === 'file'" class="space-y-4 p-4 bg-blue-50 rounded-xl border border-blue-200">
                        <h4 class="text-xs font-bold text-blue-700 uppercase">File Upload Settings</h4>
                        
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 cursor-pointer p-3 bg-white rounded-xl border border-blue-100 hover:bg-blue-50 transition-colors">
                                <input type="checkbox" v-model="selectedElement.allow_multiple" class="w-4 h-4 text-blue-600 rounded">
                                <span class="text-sm font-semibold text-gray-700">Allow multiple files</span>
                            </label>
                        </div>

                        <div class="space-y-2" v-if="selectedElement.allow_multiple">
                            <label class="text-xs font-bold text-gray-600 uppercase">Max Files</label>
                            <input v-model.number="selectedElement.max_files" type="number" min="1" max="20" class="w-full p-2.5 bg-white border border-blue-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500" placeholder="e.g., 5">
                            <p class="text-xs text-gray-500">Maximum number of files (1-20)</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-600 uppercase">Allowed File Types</label>
                            <select v-model="selectedElement.file_type_restriction" class="w-full p-2.5 bg-white border border-blue-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-blue-500">
                                <option value="">All Files</option>
                                <option value="image">Images Only (JPG, PNG, GIF, WebP)</option>
                                <option value="document">Documents Only (PDF, DOC, DOCX, XLS, XLSX)</option>
                                <option value="image_document">Images & Documents</option>
                                <option value="custom">Custom Types</option>
                            </select>
                        </div>

                        <div class="space-y-2" v-if="selectedElement.file_type_restriction === 'custom'">
                            <label class="text-xs font-bold text-gray-600 uppercase">Custom File Extensions</label>
                            <input v-model="selectedElement.custom_file_types" type="text" class="w-full p-2.5 bg-white border border-blue-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500" placeholder=".jpg,.png,.pdf">
                            <p class="text-xs text-gray-500">Comma-separated, e.g., .jpg,.png,.pdf</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-600 uppercase">Max File Size (MB)</label>
                            <input v-model.number="selectedElement.max_file_size" type="number" min="1" max="50" class="w-full p-2.5 bg-white border border-blue-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500" placeholder="e.g., 5">
                            <p class="text-xs text-gray-500">Maximum size per file (1-50 MB)</p>
                        </div>

                        <div class="space-y-2">
                            <label class="flex items-center gap-3 cursor-pointer p-3 bg-white rounded-xl border border-blue-100 hover:bg-blue-50 transition-colors">
                                <input type="checkbox" v-model="selectedElement.show_preview" class="w-4 h-4 text-blue-600 rounded">
                                <span class="text-sm font-semibold text-gray-700">Show file preview</span>
                            </label>
                            <p class="text-xs text-gray-500">Display preview for images and thumbnails for documents</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Field Width</label>
                        <select v-model="selectedElement.field_width" class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-blue-500 transition-all">
                            <option value="full">Full Width (100%)</option>
                            <option value="half">Half Width (50%)</option>
                            <option value="third">Third Width (33%)</option>
                            <option value="quarter">Quarter Width (25%)</option>
                        </select>
                        <p class="text-xs text-gray-400">Controls how much horizontal space this field takes</p>
                    </div>

                    <div class="space-y-2" v-if="['select', 'radio', 'checkbox'].includes(selectedElement.field_type)">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Options (Comma separated)</label>
                        <textarea v-model="selectedElement.options" class="w-full p-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm h-32" placeholder="Option 1, Option 2, etc."></textarea>
                    </div>

                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-100" v-if="selectedElement.field_type === 'customer'">
                        <h4 class="text-[10px] font-bold text-blue-600 uppercase mb-2">Dynamic Field</h4>
                        <p class="text-[10px] text-blue-400 leading-normal">This field will automatically populate with customers from the database.</p>
                    </div>

                    <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <h4 class="text-[10px] font-bold text-blue-600 uppercase mb-2">Advanced Info</h4>
                        <p class="text-[10px] text-blue-400 leading-normal">Configure JSON-based validation and conditional logic in the next update.</p>
                    </div>
                </div>

                <!-- Section Specific Config -->
                <div v-if="selectedType === 'section'" class="space-y-6">
                    <p class="text-sm text-gray-500 italic">Editing section properties in-place in the builder.</p>
                    <button @click="removeSection(selectedIndex)" class="w-full py-3 bg-red-50 text-red-600 rounded-xl text-sm font-bold hover:bg-red-100 transition-colors">
                        Delete Section
                    </button>
                </div>

                <!-- Subsection Specific Config -->
                <div v-if="selectedType === 'subsection'" class="space-y-6">
                    <p class="text-sm text-purple-600 italic">Editing subsection properties in-place in the builder.</p>
                    <div class="p-4 bg-purple-50 rounded-xl border border-purple-200">
                        <p class="text-xs text-purple-700">Subsections appear as nested cards inside their parent section.</p>
                    </div>
                </div>
            </div>

            <div v-else class="p-12 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                <p class="text-sm">Select an element to configure it</p>
            </div>
        </aside>
    </div>

    <!-- Revision History Modal -->
    <div v-if="showRevisionModal" @click="showRevisionModal = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div @click.stop class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl max-h-[80vh] overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white">Revision History</h2>
                    <p class="text-blue-100 text-sm">View and restore previous versions</p>
                </div>
                <button @click="showRevisionModal = false" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-8 overflow-y-auto max-h-[calc(80vh-120px)]">
                <div v-if="loadingRevisions" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"></div>
                    <p class="text-gray-500 mt-4">Loading revisions...</p>
                </div>
                
                <div v-else-if="revisions.length === 0" class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-gray-500 font-medium">No revision history yet</p>
                    <p class="text-gray-400 text-sm mt-2">Revisions are created automatically when you save changes</p>
                </div>
                
                <div v-else class="space-y-4">
                    <div v-for="revision in revisions" :key="revision.id" class="bg-gray-50 rounded-2xl p-6 border border-gray-200 hover:border-blue-300 transition-all group">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold text-sm">
                                        #{{ revision.revision_number }}
                                    </span>
                                    <h3 class="font-bold text-gray-800">{{ revision.title }}</h3>
                                </div>
                                <p class="text-sm text-gray-500 mb-3" v-if="revision.description">{{ revision.description }}</p>
                                <div class="flex items-center gap-4 text-xs text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ formatDate(revision.created_at) }}
                                    </span>
                                </div>
                            </div>
                            <button @click="restoreRevision(revision.id)" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-all opacity-0 group-hover:opacity-100 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Restore
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
const { createApp, ref, onMounted, computed } = Vue;

createApp({
    setup() {
        const form = ref({
            id: <?php echo json_encode($id); ?>,
            title: '',
            description: '',
            form_type: <?php echo json_encode($type); ?>,
            customer_id: <?php echo json_encode($customer_id); ?> || '',
            status: 'active',
            sections: []
        });

        const selectedType = ref(null); // 'section' or 'field'
        const selectedIndex = ref(null);
        const selectedSectionIndex = ref(null);
        const selectedSubsectionIndex = ref(null);
        const saving = ref(false);
        const customers = ref([]);
        const showCustomerError = ref(false);
        const showRevisionModal = ref(false);
        const revisions = ref([]);
        const loadingRevisions = ref(false);

        const selectedElement = computed(() => {
            if (selectedType.value === 'section') {
                return form.value.sections[selectedIndex.value];
            } else if (selectedType.value === 'subsection') {
                return form.value.sections[selectedSectionIndex.value].subsections[selectedIndex.value];
            } else if (selectedType.value === 'field') {
                if (selectedSubsectionIndex.value !== null) {
                    return form.value.sections[selectedSectionIndex.value].subsections[selectedSubsectionIndex.value].fields[selectedIndex.value];
                }
                return form.value.sections[selectedSectionIndex.value].fields[selectedIndex.value];
            }
            return null;
        });

        const addSection = () => {
            form.value.sections.push({
                title: 'New Section',
                description: '',
                fields: [],
                subsections: []
            });
            selectElement('section', form.value.sections.length - 1);
        };

        const addSubsection = () => {
            if (selectedType.value === 'section' && selectedIndex.value !== null) {
                if (!form.value.sections[selectedIndex.value].subsections) {
                    form.value.sections[selectedIndex.value].subsections = [];
                }
                form.value.sections[selectedIndex.value].subsections.push({
                    title: 'New Subsection',
                    description: '',
                    fields: []
                });
                const subIndex = form.value.sections[selectedIndex.value].subsections.length - 1;
                selectElement('subsection', selectedIndex.value, subIndex);
            }
        };

        const removeSection = (index) => {
            if (confirm('Delete this section and all its fields?')) {
                form.value.sections.splice(index, 1);
                selectedType.value = null;
            }
        };

        const addField = (sectionIndex, subsectionIndex = null) => {
            const newField = {
                label: 'Untitled Question',
                field_type: 'text',
                placeholder: '',
                is_required: false,
                options: '',
                field_width: 'full',
                field_config: {},
                validation_rules: {},
                conditional_logic: {},
                allow_negative: true,
                allow_multiple: false,
                max_files: 5,
                file_type_restriction: '',
                custom_file_types: '',
                max_file_size: 5,
                show_preview: true
            };
            
            if (subsectionIndex !== null) {
                form.value.sections[sectionIndex].subsections[subsectionIndex].fields.push(newField);
                selectElement('field', sectionIndex, form.value.sections[sectionIndex].subsections[subsectionIndex].fields.length - 1, subsectionIndex);
            } else {
                form.value.sections[sectionIndex].fields.push(newField);
                selectElement('field', sectionIndex, form.value.sections[sectionIndex].fields.length - 1);
            }
        };

        const removeField = (sectionIndex, fieldIndex, subsectionIndex = null) => {
            if (subsectionIndex !== null) {
                form.value.sections[sectionIndex].subsections[subsectionIndex].fields.splice(fieldIndex, 1);
            } else {
                form.value.sections[sectionIndex].fields.splice(fieldIndex, 1);
            }
            selectedType.value = null;
        };

        const moveField = (sIndex, fIndex, direction) => {
            const fields = form.value.sections[sIndex].fields;
            const newIndex = fIndex + direction;
            if (newIndex >= 0 && newIndex < fields.length) {
                const temp = fields[fIndex];
                fields[fIndex] = fields[newIndex];
                fields[newIndex] = temp;
            }
        };

        const selectElement = (type, index, fIndex = null, subIndex = null) => {
            selectedType.value = type;
            selectedIndex.value = fIndex !== null ? fIndex : index;
            selectedSectionIndex.value = index;
            selectedSubsectionIndex.value = subIndex;
        };

        const autoResize = (e) => {
            e.target.style.height = 'auto';
            e.target.style.height = e.target.scrollHeight + 'px';
        };

        const saveForm = async () => {
            if (!form.value.customer_id) {
                showCustomerError.value = true;
                alert('Please select a customer before saving.');
                return;
            }
            
            saving.value = true;
            try {
                const response = await fetch('../../api/surveys_v2.php?action=save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(form.value)
                });
                const res = await response.json();
                if (res.success) {
                    alert('Form saved successfully!');
                    if (!form.value.id) {
                        window.location.href = `form-maker-v2.php?id=${res.id}`;
                    }
                } else {
                    alert('Error: ' + res.message);
                }
            } catch (err) {
                alert('An error occurred while saving');
            } finally {
                saving.value = false;
            }
        };

        const loadForm = async () => {
            if (!form.value.id) return;
            try {
                const response = await fetch(`../../api/surveys_v2.php?action=load&id=${form.value.id}`);
                const res = await response.json();
                if (res.success) {
                    form.value = { ...res.survey, sections: res.sections };
                }
            } catch (err) {
                console.error('Failed to load form', err);
            }
        };

        const previewForm = () => {
            if (!form.value.id) {
                alert('Please save the form first to preview.');
                return;
            }
            window.open(`../surveys/preview-v2.php?id=${form.value.id}`, '_blank');
        };

        const loadCustomers = async () => {
            try {
                const response = await fetch('../../api/surveys_v2.php?action=get_customers');
                const res = await response.json();
                if (res.success) {
                    customers.value = res.customers;
                }
            } catch (err) {
                console.error('Failed to load customers', err);
            }
        };

        const showRevisions = async () => {
            if (!form.value.id) {
                alert('Please save the form first to view revision history.');
                return;
            }
            showRevisionModal.value = true;
            loadingRevisions.value = true;
            try {
                const response = await fetch(`../../api/surveys_v2.php?action=get_revisions&id=${form.value.id}`);
                const res = await response.json();
                if (res.success) {
                    revisions.value = res.revisions;
                }
            } catch (err) {
                console.error('Failed to load revisions', err);
            } finally {
                loadingRevisions.value = false;
            }
        };

        const restoreRevision = async (revisionId) => {
            if (!confirm('Are you sure you want to restore this revision? Your current changes will be saved as a new revision before restoring.')) {
                return;
            }
            try {
                const response = await fetch('../../api/surveys_v2.php?action=restore_revision', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ revision_id: revisionId })
                });
                const res = await response.json();
                if (res.success) {
                    alert('Revision restored successfully! Reloading...');
                    showRevisionModal.value = false;
                    await loadForm();
                } else {
                    alert('Error: ' + res.message);
                }
            } catch (err) {
                alert('An error occurred while restoring revision');
            }
        };

        const formatDate = (dateString) => {
            const date = new Date(dateString);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        const initializeSortable = () => {
            console.log('🔧 Initializing Sortable...');
            const sectionsContainer = document.getElementById('sections-container');
            console.log('📦 Sections container:', sectionsContainer);
            console.log('📊 Number of sections:', form.value.sections.length);
            
            if (sectionsContainer) {
                const sectionItems = sectionsContainer.querySelectorAll('.section-item');
                console.log('🎯 Found section items:', sectionItems.length);
                
                if (sectionItems.length > 0) {
                    console.log('✅ Creating Sortable instance...');
                    const sortableInstance = new Sortable(sectionsContainer, {
                        animation: 200,
                        handle: '.drag-handle',
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        forceFallback: true,
                        fallbackTolerance: 3,
                        onStart: function(evt) {
                            console.log('🚀 Drag started!', {
                                oldIndex: evt.oldIndex,
                                item: evt.item
                            });
                        },
                        onMove: function(evt) {
                            console.log('🔄 Moving...', {
                                from: evt.from,
                                to: evt.to
                            });
                        },
                        onEnd: function(evt) {
                            console.log('🎯 Drag ended!', {
                                oldIndex: evt.oldIndex,
                                newIndex: evt.newIndex
                            });
                            const oldIndex = evt.oldIndex;
                            const newIndex = evt.newIndex;
                            if (oldIndex !== newIndex) {
                                console.log(`📝 Moving section from ${oldIndex} to ${newIndex}`);
                                const movedSection = form.value.sections.splice(oldIndex, 1)[0];
                                form.value.sections.splice(newIndex, 0, movedSection);
                                console.log('✅ Section moved successfully!');
                            }
                        }
                    });
                    console.log('✅ Sortable initialized successfully!', sortableInstance);
                } else {
                    console.warn('⚠️ No section items found to make sortable');
                }
            } else {
                console.error('❌ Sections container not found!');
            }
        };

        onMounted(async () => {
            await loadCustomers();
            await loadForm();
            
            // Wait for Vue to render sections, then initialize Sortable
            // Using longer timeout to ensure DOM is fully ready
            setTimeout(() => {
                console.log('⏰ Timeout reached - initializing Sortable now');
                initializeSortable();
                
                // Initialize Sortable for field palette (drag to add)
                const fieldPalette = document.getElementById('field-palette');
                if (fieldPalette) {
                    console.log('🎨 Initializing field palette sortable');
                    new Sortable(fieldPalette, {
                        group: {
                            name: 'fields',
                            pull: 'clone',
                            put: false
                        },
                        sort: false,
                        animation: 150
                    });
                }
            }, 1500);
            
            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                // Ctrl+S to save
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    console.log('⌨️ Ctrl+S pressed - saving...');
                    saveForm();
                }
                // Ctrl+P to preview
                if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                    e.preventDefault();
                    console.log('⌨️ Ctrl+P pressed - previewing...');
                    previewForm();
                }
                // Delete key to remove selected element
                if (e.key === 'Delete' && selectedType.value) {
                    e.preventDefault();
                    console.log('⌨️ Delete pressed');
                    if (selectedType.value === 'section' && selectedIndex.value !== null) {
                        removeSection(selectedIndex.value);
                    } else if (selectedType.value === 'field' && selectedSectionIndex.value !== null && selectedIndex.value !== null) {
                        removeField(selectedSectionIndex.value, selectedIndex.value, selectedSubsectionIndex.value);
                    }
                }
            });
        });

        return {
            form, addSection, addSubsection, removeSection, addField, removeField, moveField,
            selectElement, selectedType, selectedIndex, selectedSectionIndex, selectedSubsectionIndex,
            selectedElement, autoResize, saveForm, saving, previewForm, customers,
            showCustomerError, showRevisions, showRevisionModal, revisions, loadingRevisions,
            restoreRevision, formatDate, initializeSortable
        };
    }
}).mount('#app');
</script>

</body>
</html>
