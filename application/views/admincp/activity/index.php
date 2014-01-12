<div class="row-fluid">
    <div class="span12">
        <div class="social-box">
            <div class="header">
                <h4><?php echo $template['title']; ?>
            </div>
            <!-- BEGIN TABLE BODY -->
            <div class="body">
                <input type="text" id="keyword" class="input-block-level" placeholder="<?php echo lang('keyword_more_than_2_letter'); ?>">
                <table class="footable editable table" data-filter="#keyword" data-page-size="20">
                    <thead>
                        <tr>
                            <th data-type="numeric">#</th>
                            <th><?php echo lang('name'); ?></th>
                            <th><?php echo lang('location'); ?></th>
                            <th><?php echo lang('start_at'); ?></th>
                            <th data-sort-ignore="true"><?php echo lang('operate'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach($list as $item): ?>
                        <tr>
                            <td><?php echo $item['id']; ?></td>
                            <td>
                                <a href="javascript:void(0);" data-type="text" data-name="name" data-pk="<?php echo $item['id']; ?>" data-placeholder="Required" data-original-title="<?php echo lang('name_title'); ?>"<?php if(! $item['name'] ) echo ' class="editable-click editable-empty"'; ?>><?php echo $item['name'] ? $item['name'] : 'Empty'; ?></a>
                            </td>
                            <td>
                                <a href="javascript:void(0);" data-type="text" data-name="location" data-pk="<?php echo $item['id']; ?>" data-placeholder="Required" data-original-title="<?php echo lang('location_title'); ?>"<?php if(! $item['location'] ) echo ' class="editable-click editable-empty"'; ?>><?php echo $item['location'] ? $item['location'] : 'Empty'; ?></a>
                            </td>
                            <td>
                                <a href="javascript:void(0);" data-type="datetime" data-name="start_at" data-pk="<?php echo $item['id']; ?>" data-placeholder="Required" data-original-title="<?php echo lang('start_at_title'); ?>"<?php if(! $item['start_at'] ) echo ' class="editable-click editable-empty"'; ?>><?php echo $item['start_at'] ? date('Y-m-d H:i', $item['start_at']) : 'Empty'; ?></a>
                            </td>
                            <td><span data-toggle="tooltip" title="<?php echo date('Y-m-d H:i:s', $item['created']); ?>"><?php echo time_past($item['created']); ?></span></td>
                            <td>
                                <a href="<?php echo site_url('admincp/activity/modify/'.$item['id']); ?>" title="<?php echo lang('edit'); ?>"><i class="icon-edit"></i><?php echo lang('edit'); ?></a>
                                <a href="<?php echo site_url('admincp/activity/destroy/'.$item['id']); ?>" title="<?php echo lang('delete'); ?>" onclick="return confirm('<?php echo lang('delete_warning'); ?>');"><i class="icon-trash"></i><?php echo lang('delete'); ?></a>
                            </td>
                        </tr>
<?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="11">
                                <div class="pagination pagination-centered"></div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- END TABLE BODY -->
        </div>
    </div>
</div>
