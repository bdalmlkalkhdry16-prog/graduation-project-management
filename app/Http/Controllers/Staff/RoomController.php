<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::orderBy('name')->paginate(20);

        return view('staff.rooms.index', compact('rooms'));
    }

    public function create(): View
    {
        return view('staff.rooms.create');
    }

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        Room::create($request->validated());

        return redirect()->route('staff.rooms.index')->with('success', 'تم إنشاء القاعة/المعمل بنجاح.');
    }

    public function edit(Room $room): View
    {
        return view('staff.rooms.edit', compact('room'));
    }

    public function update(UpdateRoomRequest $request, Room $room): RedirectResponse
    {
        $room->update($request->validated());

        return redirect()->route('staff.rooms.index')->with('success', 'تم تحديث بيانات القاعة/المعمل.');
    }
}