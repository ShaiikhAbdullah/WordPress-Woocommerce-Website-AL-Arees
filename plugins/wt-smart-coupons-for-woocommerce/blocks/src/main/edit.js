import { useBlockProps } from '@wordpress/block-editor';
export const Edit = ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps();
    
    return (
        <div {...blockProps}>
        </div>
    );
};