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
                            <th><?php echo lang('realname'); ?></th>
                            <th><?php echo lang('community'); ?></th>
                            <th><?php echo lang('signed'); ?></th>
                            <th><?php echo lang('barcode'); ?></th>
                            <th data-sort-ignore="true"><?php echo lang('operate'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
<?php if ( empty($list) ): ?>
                        <tr>
                            <td colspan="11"><a href="<?php echo site_url(CLASS_URI.'/namelist_import/'.$aid); ?>">无数据，上传表格</a></td>
                        </tr>
<?php else: ?>
<?php foreach($list as $item): ?>
                        <tr>
                            <td><?php echo $item['ordered_id']; ?></td>
                            <td>
                                <a href="javascript:void(0);" data-type="text" data-name="realname" data-pk="<?php echo $item['id']; ?>" data-placeholder="Required" data-original-title="<?php echo lang('realname_title'); ?>"<?php if(! $item['realname'] ) echo ' class="editable-click editable-empty"'; ?>><?php echo $item['realname'] ? $item['realname'] : 'Empty'; ?></a>
                            </td>
                            <td>
                                <a href="javascript:void(0);" data-type="text" data-name="community" data-pk="<?php echo $item['id']; ?>" data-placeholder="Required" data-original-title="<?php echo lang('community_title'); ?>"<?php if(! $item['community'] ) echo ' class="editable-click editable-empty"'; ?>><?php echo $item['community'] ? $item['community'] : 'Empty'; ?></a>
                            </td>
                            <td><?php echo $item['signed'] ? '<span class="label label-success">'.date('H:i:s', $item['signed']).'</span>' : '<span class="label">未到</span>'; ?></td>
                            <td><?php echo $item['barcode']; ?></td>
                            <td>
                                <a href="<?php echo site_url('admincp/activity/namelist_del/'.$item['id']); ?>" title="<?php echo lang('delete'); ?>" onclick="return confirm('<?php echo lang('delete_warning'); ?>');"><i class="icon-trash"></i><?php echo lang('delete'); ?></a>
                            </td>
                        </tr>
<?php endforeach; ?>
<?php endif; ?>
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
