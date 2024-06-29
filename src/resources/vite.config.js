
import vue from '@vitejs/plugin-vue'


export default ({ command }) => ({
  base: command === 'serve' ? '' : '/dist/',
  resolve: {
    alias: {
      '~': '/node_modules'
    }
  },
  plugins: [
    vue()
  ],
  build: {
    manifest: false,
    outDir: './dist/',
    rollupOptions: {
      input: {
        field: './src/js/field.js',
      },
      output: {
        entryFileNames: `js/[name].js`,
        chunkFileNames: `js/[name].js`,
        assetFileNames: `[ext]/[name].[ext]`
      }
    }
  }
})