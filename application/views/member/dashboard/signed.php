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
                            <td><?php echo $item['realname']; ?></td>
                            <td><?php echo $item['community']; ?></td>
                            <td><?php echo $item['signed'] ? '<span class="label label-success">'.date('H:i:s', $item['signed']).'</span>' : '<span class="label">未到</span>'; ?></td>
                            <td><?php echo $item['barcode']; ?></td>
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
