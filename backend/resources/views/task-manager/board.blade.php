@extends('layouts.app')
@section('title', 'Task Board')
@section('page-title', 'Task Manager — Task Board')
@section('page-desc', 'Assign, filter and track internal work tasks to completion')

@section('content')
@include('task-manager._board-table')
@endsection
