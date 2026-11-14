@extends('errors::minimal')

@section('title', __('Service unavailable'))
@section('code', '503')
@section('message', __('Service unavailable'))
@section('description', __('The application is temporarily unavailable. Please try again shortly.'))
