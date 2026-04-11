<!-- Requests Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">#</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Priority</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Request Details</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Requested By</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    if (empty($result['records'])):
                    ?>
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-sm">No requests found</p>
                            </td>
                        </tr>
                    <?php 
                    else:
                        $serialNo = (($result['page'] - 1) * $result['limit']) + 1;
                        foreach ($result['records'] as $request):
                            $priorityColors = [
                                'urgent' => 'bg-red-100 text-red-800 border-red-200',
                                'high' => 'bg-orange-100 text-orange-800 border-orange-200',
                                'medium' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'low' => 'bg-gray-100 text-gray-800 border-gray-200'
                            ];
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'approved' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800'
                            ];
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-600"><?php echo $serialNo++; ?></td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold border <?php echo $priorityColors[$request['priority']]; ?>">
                                    <?php echo ucfirst($request['priority']); ?>
                                </span>
                            </td>
                            <td class="px-3 py-2">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($request['request_title']); ?></div>
                                <div class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars(substr($request['request_description'], 0, 80)) . (strlen($request['request_description']) > 80 ? '...' : ''); ?></div>
                                <div class="text-xs text-blue-600 mt-1">
                                    <span class="font-medium">Type:</span> <?php echo ucwords(str_replace('_', ' ', $request['request_type'])); ?>
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($request['requested_by_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($request['requested_by_role']); ?></div>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColors[$request['status']]; ?>">
                                    <?php echo ucfirst($request['status']); ?>
                                </span>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <div class="text-xs text-gray-900"><?php echo date('M d, Y', strtotime($request['created_at'])); ?></div>
                                <div class="text-xs text-gray-500"><?php echo date('h:i A', strtotime($request['created_at'])); ?></div>
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <button onclick="viewRequest(<?php echo $request['id']; ?>)" class="btn btn-sm btn-secondary" title="View Details">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <button onclick="approveRequest(<?php echo $request['id']; ?>)" class="btn btn-sm btn-success" title="Approve">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                        <button onclick="rejectRequest(<?php echo $request['id']; ?>)" class="btn btn-sm btn-danger" title="Reject">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination (same as sites page) -->
        <?php if ($result['pages'] > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-b-lg">
            <div class="flex-1 flex justify-between sm:hidden">
                <?php if ($result['page'] > 1): ?>
                    <a href="?page=<?php echo $result['page'] - 1; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                <?php endif; ?>
                <?php if ($result['page'] < $result['pages']): ?>
                    <a href="?page=<?php echo $result['page'] + 1; ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?php echo (($result['page'] - 1) * $result['limit']) + 1; ?></span>
                        to <span class="font-medium"><?php echo min($result['page'] * $result['limit'], $result['total']); ?></span>
                        of <span class="font-medium"><?php echo $result['total']; ?></span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        <?php
                        $current = $result['page'];
                        $total = $result['pages'];
                        
                        if ($current > 1): ?>
                            <a href="?page=<?php echo $current - 1; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </a>
                        <?php endif;
                        
                        for ($i = 1; $i <= $total; $i++) {
                            if ($i == $current) {
                                echo '<span class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600 z-10">' . $i . '</span>';
                            } else {
                                echo '<a href="?page=' . $i . '" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">' . $i . '</a>';
                            }
                        }
                        
                        if ($current < $total): ?>
                            <a href="?page=<?php echo $current + 1; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
