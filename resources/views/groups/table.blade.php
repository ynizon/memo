<div class="border-bottom py-3 px-3 d-sm-flex align-items-center">

    <div class="input-group w-sm-25 ms-auto py-2 py-lg-0">
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
                {{__('Group')}}</th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7">
                {{__('Users')}}</th>
            <th class="text-secondary text-xs font-weight-semibold opacity-7">
                {{__('Action')}}</th>
        </tr>
        </thead>
        <tbody>
            @foreach($groups as $group)
                <tr>
                    <td>
                        <div class="d-flex px-2">
                            <div class="rounded-circle bg-gray-100 me-2 my-2">
                                <a href="/groups/{{$group->id}}/edit">
                                    <i class="pad fa fa-list fa-group"></i>
                                </a>
                            </div>
                            <div class="my-auto">
                                <a href="/groups/{{$group->id}}/edit" class="list_task_item">
                                    <h6 class="mb-0 text-sm">
                                        {{$group->name}}
                                    </h6>
                                </a>
                            </div>
                        </div>
                    </td>
                    <td>
                        @foreach ($group->users()->get() as $user)
                            {{ $user->email }}<br/>
                        @endforeach
                    </td>
                    <td>
                        @if ($group->user_id == Auth::user()->id)
                            <form action="{{ route('groups.addto', $group->id) }}" method="post">
                                @csrf
                                <input type="text" placeHolder="Email" style="max-width:50%;display:inline" class="form-control" id="email" name="email" value="" required>
                                &nbsp;&nbsp;
                                <button type="submit" class="btn btn-primary" style="margin:auto;">
                                    <i class="pad fas fa-save" aria-hidden="true"></i>{{__("Add to this group")}}
                                </button>
                            </form>
                        @endif
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
    };
</script>