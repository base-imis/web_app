@extends('errors::minimal')

@section('title', __('Server error'))
@section('code', '500')
@section('message', __('Server error'))
@section('description', __('Something went wrong while loading this page. Please try again later or return to the home page.'))
