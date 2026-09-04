<?php $__env->startSection('title', 'Task Board'); ?>
<?php $__env->startSection('page-title', 'Task Manager — Task Board'); ?>
<?php $__env->startSection('page-desc', 'Assign, filter and track internal work tasks to completion'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('task-manager._board-table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views/task-manager/board.blade.php ENDPATH**/ ?>