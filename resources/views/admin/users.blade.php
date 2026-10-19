@extends('layouts.app')
@section('title', 'Users')

@php
    // Shared look for the popup's fields.
    $field = 'w-full rounded-md border border-ink-200 bg-white px-3 py-2';
    $addFailed = $errors->any() && old('_form') === 'add_user';
@endphp

@section('content')
    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">Users and roles</h1>
            <p class="mt-1 text-ink-500">Change who is Staff, Manager or Admin. People who sign up themselves start as Staff.</p>
        </div>
        <button type="button" onclick="openUserModal()"
                class="rounded-md bg-signal px-4 py-2.5 text-sm font-medium text-white hover:bg-signal-dark">Add person</button>
    </header>

    {{-- USERS TABLE --}}
    <div class="overflow-x-auto rounded-lg border border-ink-200 bg-white">
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead class="border-b border-ink-200 bg-ink-50 text-ink-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Name</th>
                    <th class="px-4 py-3 font-medium">Open tasks</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                    <th class="px-4 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @foreach ($users as $u)
                    @php $isMe = $u->is(auth()->user()); @endphp
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium">
                                {{ $u->name }}
                                @if ($isMe)<span class="text-xs text-ink-500">(you)</span>@endif
                            </p>
                            <p class="text-xs text-ink-500">
                                {{ $u->email }}@if ($u->job_title) · {{ $u->job_title }}@endif
                            </p>
                        </td>

                        <td class="px-4 py-3">{{ $u->open_count }}</td>

                        <td class="px-4 py-3">
                            @if ($isMe)
                                {{ $u->roleLabel() }}
                            @else
                                <form method="POST" action="{{ route('admin.users.update', $u) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="role-{{ $u->id }}">Role for {{ $u->name }}</label>
                                    <select id="role-{{ $u->id }}" name="role" class="rounded-md border border-ink-200 bg-white px-2 py-1">
                                        @foreach (\App\Models\User::ROLES as $key => $label)
                                            <option value="{{ $key }}" @selected($u->role === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="rounded-md border border-ink-200 px-3 py-1 font-medium hover:bg-ink-50">Save role</button>
                                </form>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right">
                            @unless ($isMe)
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline"
                                      data-confirm="{{ 'Remove '.$u->name.'? Their tasks will become unassigned.' }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded px-2 py-1 text-flare hover:bg-ink-100">Remove</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>

    {{-- ADD PERSON POPUP --}}
    <dialog id="user-modal" class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-xl bg-white p-0 shadow-xl">
        <form method="POST" action="{{ route('admin.users.store') }}" class="p-6">
            @csrf
            <input type="hidden" name="_form" value="add_user">

            <h2 class="font-display text-xl font-bold">Add a person</h2>
            <p class="mt-1 text-sm text-ink-500">They can sign in right away with this email and temporary password.</p>

            @if ($addFailed)
                <div role="alert" class="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-900">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <label for="n-name" class="mb-1 block font-medium">Full name</label>
                    <input id="n-name" name="name" required maxlength="120" value="{{ old('name') }}" class="{{ $field }}">
                </div>

                <div>
                    <label for="n-email" class="mb-1 block font-medium">Work email</label>
                    <input id="n-email" name="email" type="email" required value="{{ old('email') }}" class="{{ $field }}">
                </div>

                <div>
                    <label for="n-title" class="mb-1 block font-medium">Job title <span class="font-normal text-ink-500">(optional)</span></label>
                    <input id="n-title" name="job_title" maxlength="120" value="{{ old('job_title') }}" class="{{ $field }}">
                </div>

                <div>
                    <label for="n-role" class="mb-1 block font-medium">Role</label>
                    <select id="n-role" name="role" class="{{ $field }}">
                        @foreach (\App\Models\User::ROLES as $key => $label)
                            <option value="{{ $key }}" @selected(old('role', 'staff') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="n-pass" class="mb-1 block font-medium">Temporary password <span class="font-normal text-ink-500">(8+ characters)</span></label>
                    <input id="n-pass" name="password" type="text" required minlength="8" autocomplete="off" class="{{ $field }}">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="userModal.close()"
                        class="rounded-md border border-ink-200 px-4 py-2 text-sm font-medium hover:bg-ink-50">Cancel</button>
                <button class="rounded-md bg-signal px-4 py-2 text-sm font-medium text-white hover:bg-signal-dark">Add person</button>
            </div>
        </form>
    </dialog>

    <script>
        const userModal = document.getElementById('user-modal');

        function openUserModal() {
            userModal.showModal();
            document.getElementById('n-name').focus();
        }

        // Clicking the dark backdrop closes the popup.
        userModal.addEventListener('click', (e) => {
            if (e.target === userModal) userModal.close();
        });

        @if ($addFailed)
            // Validation failed: reopen the popup with the errors and the typed values.
            openUserModal();
        @endif
    </script>
@endsection
