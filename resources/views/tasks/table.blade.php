<div class="border-bottom py-3 px-3 d-sm-flex align-items-center">
    <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
        <input type="radio" class="btn-check" name="btnradiotable" id="btnradiotableX" autocomplete="off" checked="">
        <label class="btn btn-white px-3 mb-0 datafiltreCategory" data-category="" for="btnradiotableX">{{__("All")}}</label>
        @foreach($categories as $category)
            <input type="radio" class="btn-check" name="btnradiotable" id="btnradiotable{{$loop->index}}" autocomplete="off">
            <label class="btn btn-white px-3 mb-0 datafiltreCategory" data-category="{{$category->id}}" for="btnradiotable{{$loop->index}}"><i class="fa {{$category->icon}}"></i></label>
        @endforeach
    </div>
    <div class="input-group w-sm-25 ms-auto">
        <span class="input-group-text text-body">
        <svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z">
        </path>
        </svg>
        </span>
        <input type="text" id="datatable-search" class="form-control" placeholder="{{__("Search")}}">
    </div>
</div>
<div class="table-responsive">
    <table class="table text-secondary text-center" id="datatable">
        <thead class="bg-gray-100">
        <tr>
            <th class="text-secondary text-xs font-weight-semibold opacity-7">
                {{__('Name')}}</th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7 ps-2">
                {{__('Amount')}}</th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7 ps-2">
                {{__('Date')}}
            </th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7 ps-2">
                {{__('Information')}}</th>
            <th
                class="text-center text-secondary text-xs font-weight-semibold opacity-7">
                {{__('Action')}}
            </th>
        </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td>
                        <div class="d-flex px-2">
                            <div class="rounded-circle bg-gray-100 me-2 my-2">
                                <i class="fa fa-list {{$task->category->icon}}"></i>
                                &nbsp;&nbsp;&nbsp;
                            </div>
                            <div class="my-auto">
                                <h6 class="mb-0 text-sm">{{$task->name}}</h6>
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="text-sm font-weight-normal mb-0">@if ($task->price > 0){{$task->price}} €@endif</p>
                    </td>
                    <td>
                        <span class="text-sm font-weight-normal">{{formatDate($task->created_at)}}</span>
                    </td>
                    <td>
                        <span class="text-sm font-weight-normal">{{$task->information}}</span>
                    </td>
                    <td class="align-middle">
                        <a href="/tasks/{{$task->id}}/edit"><i class="fas fa-edit" aria-hidden="true"></i></a>&nbsp;
                        <form style="display:inline;" action="{{ route('tasks.destroy', $task->id) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="nobtn"><i class="fas fa-trash" aria-hidden="true"></i></button>
                        </form>
                        <span class="invisible">Category-{{$task->category->id}}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script src="/assets/js/plugins/datatables.js"></script>
<script>
    window.onload = function(e){
        const dataTableBasic = new simpleDatatables.DataTable("#datatable", {
            searchable: false,
            fixedHeight: true,
            bLengthChange: false,
            paging: true,
            showNEntries: false,
            perPage: 10,
        });

        $('#datatable-search').keyup(function () {
            dataTableBasic.search($(this).val()).draw();
        })

        $('.datafiltreCategory').click(function () {
            dataTableBasic.search('Category-' + $(this).attr('data-category')).draw();
        })
    };
</script>
